<?php

namespace App\Services;

use App\Http\Requests\FeeRequest;
use App\Models\Fee;
use App\Models\Student;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeeService
{
    public function update(int $tuition_id, array $data)
    {
        DB::beginTransaction();

        try {
            $fee = Fee::where('uuid', $data['fee_uuid'])->where('tuition_id', $tuition_id)->firstOrFail();

            $fee->update([
                'is_paid' => $data['is_paid'],
            ]);

            DB::commit();
            Cache::forget("dashboard_stats_{$tuition_id}");
            return [
                'status' => 'success',
                'msg'    => 'Fee updated successfully',
                'data'   => $fee->fresh(),
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'status' => 'error',
                'msg'    => 'Failed to update fee',
                'error'  => $e->getMessage(),
            ];
        }
    }

    public function generateFess(int $tuition_id)
    {
        DB::beginTransaction();

        try {
            $yearMonth  = now()->subMonth()->format('F Y');
            $students = Student::where('tuition_id', $tuition_id)
                ->whereHas('user', function ($q) {
                    $q->where('status', 'active'); // only active users
                })
                ->with('user')
                ->get();

            $createdCount = 0;

            foreach ($students as $student) {
                // Check if fee already exists for this month
                $exists = Fee::where('student_id', $student->id)
                    ->where('year_month', $yearMonth)
                    ->exists();

                if (!$exists) {
                    Fee::create([
                        'tuition_id'   => $student->tuition_id,
                        'student_id'   => $student->id,
                        'monthly_fees' => $student->monthly_fees ?? 0,
                        'year_month'   => $yearMonth,
                        'is_paid'      => false,
                    ]);
                    $createdCount++;
                }
            }

            DB::commit();
            Cache::forget("dashboard_stats_{$tuition_id}");
            return response()->json([
                'status' => 'success',
                'msg'    => "Fees generated successfully",
                'data'   => [
                    'year_month'      => $yearMonth,
                    'total_students'  => $students->count(),
                    'fees_created'    => $createdCount,
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'msg'    => 'Something went wrong while generating fees',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }
}
