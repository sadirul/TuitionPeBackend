<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\User;

class ClassService
{
    public function store(int $id, array $data)
    {
        $class = Classes::create([
            'tuition_id' => $id,
            'class_name' => $data['class_name'],
            'section' => $data['section'] ?? null,
        ]);

        return [
            'status' => 'success',
            'msg' => 'Class created successfully',
            'data' => $class
        ];
    }

    public function index(int $tuition_id)
    {
        $class = Classes::where('tuition_id', $tuition_id)->get();

        return [
            'status' => 'success',
            'msg' => 'Class fetched successfully',
            'data' => $class
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
        ]);

        return [
            'status' => 'success',
            'msg' => 'Class updated successfully',
            'data' => $class
        ];
    }
}
