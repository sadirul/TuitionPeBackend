<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentService
{
    public function store(int $id, array $data)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name'     => $data['name'],
                'username' => Str::of($data['name'])
                    ->lower()
                    ->replace(' ', '.')
                    ->replaceMatches('/\.+/', '.'),
                'mobile'   => $data['mobile'],
                'address'  => $data['address'],
                'role'     => 'student',
                'email'    => $data['email'] ?? null,
                'password' => $data['password'],
            ]);

            $user->update([
                'tuition_id' => $id
            ]);

            $class = Classes::select('id')->where('uuid', $data['class'])->firstOrFail();

            Student::create([
                'tuition_id'       => $id,
                'user_id'          => $user->id,
                'class_id'         => $class->id,
                'gender'           => $data['gender'],
                'guardian_name'    => $data['guardianName'],
                'guardian_contact' => $data['guardianMobile'],
                'monthly_fees'     => $data['monthlyFees'],
                'admission_year'     => date('Y'),
            ]);

            DB::commit();

            return [
                'status' => 'success',
                'msg'    => 'Student added successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'status' => 'error',
                'msg'    => 'Failed to add student',
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function index(int $tuition_id, ?string $student_uuid = null)
    {
        $query = User::with('studentInfo.class', 'studentInfo.fees')
            ->where('tuition_id', $tuition_id)
            ->where('role', 'student');

        if ($student_uuid) {
            $student = $query->where('uuid', $student_uuid)->first();

            return [
                'status' => $student ? 'success' : 'error',
                'msg'    => $student ? 'Student fetched successfully' : 'Student not found',
                'data'   => $student,
            ];
        }


        $students = $query->paginate(25);

        return [
            'status' => 'success',
            'msg' => 'Students fetched successfully',
            'data' => $students
        ];
    }


    public function update(int $tuition_id, array $data)
    {
        DB::beginTransaction();

        try {
            $user = User::where('uuid', $data['student_id'])->with('studentInfo.class')
                ->where('tuition_id', $tuition_id)
                ->where('role', 'student')
                ->firstOrFail();

            $student = $user->studentInfo;

            if (!$user) {
                return [
                    'status' => 'error',
                    'msg'    => 'Student not found',
                ];
            }

            // Update user data
            $user->update([
                'name'     => $data['name'] ?? $user->name,
                'mobile'   => $data['mobile'] ?? $user->mobile,
                'address'  => $data['address'] ?? $user->address,
                'email'    => $data['email'] ?? $user->email,
            ]);

            $classId = $student->class_id;
            if (!empty($data['class'])) {
                $class = Classes::select('id')
                    ->where('uuid', $data['class'])
                    ->where('tuition_id', $tuition_id)
                    ->firstOrFail();

                $classId = $class->id;
            }

            // Update student table
            $student->update([
                'class_id'         => $classId,
                'gender'           => $data['gender'] ?? $student->gender,
                'guardian_name'    => $data['guardianName'] ?? $student->guardian_name,
                'guardian_contact' => $data['guardianMobile'] ?? $student->guardian_contact,
                'monthly_fees'     => $data['monthlyFees'] ?? $student->monthly_fees,
            ]);

            DB::commit();

            return [
                'status' => 'success',
                'msg'    => 'Student updated successfully',
                'data' => $user
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'status' => 'error',
                'msg'    => 'Failed to update student',
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function changeClass(int $tuition_id, array $data)
    {
        return DB::transaction(function () use ($tuition_id, $data) {
            // Find class
            $class = Classes::where('uuid', $data['class'])
                ->where('tuition_id', $tuition_id)
                ->firstOrFail();

            // Bulk update students
            $updated = Student::whereIn('uuid', $data['student_ids'])
                ->where('tuition_id', $tuition_id)
                ->update([
                    'class_id' => $class->id,
                ]);

            return [
                'status'  => 'success',
                'msg' => $updated . ' students updated successfully',
            ];
        });
    }
}
