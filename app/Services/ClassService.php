<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        Cache::forget("dashboard_stats_{$id}");
        Cache::forget("classes_tuition_{$id}");

        return [
            'status' => 'success',
            'msg' => 'Class created successfully',
            'data' => $class
        ];
    }

    public function index(int $tuition_id)
    {
        $cacheKey = "classes_tuition_{$tuition_id}";
        $classes = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tuition_id) {
            return Classes::where('tuition_id', $tuition_id)
                ->withCount([
                    'students as students_count' => function ($query) {
                        $query->whereHas('user', function ($q) {
                            $q->where('status', 'active');
                        });
                    }
                ])
                ->get();
        });

        return [
            'status' => 'success',
            'msg' => 'Class fetched successfully',
            'data' => $classes
        ];
    }


    public function update(int $tuition_id, string $uuid, array $data)
    {
        return DB::transaction(function () use ($tuition_id, $uuid, $data) {
            // Find the class belonging to this tuition
            $class = Classes::where('tuition_id', $tuition_id)
                ->where('uuid', $uuid)
                ->first();

            if (!$class) {
                return [
                    'status' => 'error',
                    'msg'    => 'Class not found!',
                    'data'   => null
                ];
            }

            // Update class
            $class->update([
                'class_name' => $data['class_name'],
                'section'    => $data['section'] ?? null,
                'fee'        => $data['fee'] ?? $class->fee,
            ]);

            // Update students' monthly fee if requested
            if (!empty($data['updateAllStudentFee']) && $data['updateAllStudentFee']) {
                // Update all studentInfos for this class
                Student::where('class_id', $class->id)
                    ->whereHas('user', function ($query) use ($tuition_id) {
                        $query->where('tuition_id', $tuition_id)
                            ->where('role', 'student');
                    })
                    ->update(['monthly_fees' => $data['fee']]);
            }


            // Clear cache
            Cache::forget("classes_tuition_{$tuition_id}");

            return [
                'status' => 'success',
                'msg'    => 'Class updated successfully',
                'data'   => $class->fresh()
            ];
        });
    }
}
