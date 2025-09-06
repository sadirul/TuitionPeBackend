<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ClassService
{
    public function store(int $id, array $data)
    {
        $class = Classes::create([
            'tuition_id' => $id,
            'class_name' => $data['class_name'],
            'section' => $data['section'] ?? null,
            'fee' => $data['fee'] ?? null,
        ]);
        $cacheKey = "dashboard_stats_{$id}";
        Cache::forget($cacheKey);

        return [
            'status' => 'success',
            'msg' => 'Class created successfully',
            'data' => $class
        ];
    }

    public function index(int $tuition_id)
    {
        $classes = Classes::where('tuition_id', $tuition_id)
            ->withCount([
                'students as students_count' => function ($query) {
                    $query->whereHas('user', function ($q) {
                        $q->where('status', 'active');
                    });
                }
            ])
            ->get();


        return [
            'status' => 'success',
            'msg' => 'Class fetched successfully',
            'data' => $classes
        ];
    }

    public function update(int $tuition_id, string $uuid, array $data)
    {
        // Find the class belonging to this tuition
        $class = Classes::where('tuition_id', $tuition_id)
            ->where('uuid', $uuid)
            ->first();

        if (!$class) {
            return [
                'status' => 'error',
                'msg' => 'Class not found!',
                'data' => null
            ];
        }

        // Update with validated data
        $class->update([
            'class_name' => $data['class_name'],
            'section'    => $data['section'] ?? null,
            'fee'    => $data['fee'] ?? $class->fee,
        ]);

        return [
            'status' => 'success',
            'msg' => 'Class updated successfully',
            'data' => $class->fresh()
        ];
    }
}
