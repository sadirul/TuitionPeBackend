<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{

    public function dashboard(Request $request)
    {
        $tuitionId = $request->user()->id;
        $previousMonth = now()->subMonth()->format('F Y');

        $cacheKey = "dashboard_stats_{$tuitionId}";

        $stats = Cache::remember($cacheKey, now()->addDay(), function () use ($tuitionId, $previousMonth) {
            return DB::table('users')
                ->selectRaw('(SELECT COUNT(*) FROM users WHERE role = "student" AND status = "active" AND tuition_id = ?) as total_active_students', [$tuitionId])
                ->selectRaw('(SELECT COUNT(*) FROM users WHERE role = "student" AND status = "inactive" AND tuition_id = ?) as total_inactive_students', [$tuitionId])
                ->selectRaw('(SELECT COUNT(*) FROM classes WHERE tuition_id = ?) as total_classes', [$tuitionId])

                // -- total unpaid fees (active students only)
                ->selectRaw('(
            SELECT COUNT(*) 
            FROM fees 
            JOIN students ON fees.student_id = students.id 
            JOIN users ON students.user_id = users.id 
            WHERE fees.is_paid = 0 
              AND fees.tuition_id = ? 
              AND users.status = "active" 
              AND users.role = "student"
        ) as total_fees_due', [$tuitionId])

                // -- unpaid fees (this month, active students only)
                ->selectRaw('(
            SELECT COALESCE(SUM(fees.monthly_fees),0) 
            FROM fees 
            JOIN students ON fees.student_id = students.id 
            JOIN users ON students.user_id = users.id 
            WHERE fees.is_paid = 0 
              AND fees.tuition_id = ? 
              AND fees.year_month = ? 
              AND users.status = "active" 
              AND users.role = "student"
        ) as fees_due_this_month', [$tuitionId, $previousMonth])

                // -- paid fees (this month, active students only)
                ->selectRaw('(
            SELECT COALESCE(SUM(fees.monthly_fees),0) 
            FROM fees 
            JOIN students ON fees.student_id = students.id 
            JOIN users ON students.user_id = users.id 
            WHERE fees.is_paid = 1 
              AND fees.tuition_id = ? 
              AND fees.year_month = ? 
              AND users.status = "active" 
              AND users.role = "student"
        ) as fees_paid_this_month', [$tuitionId, $previousMonth])

                ->first();
        });


        return response()->json([
            'status' => 'success',
            'msg' => 'Dashboard data fetched successfully',
            'data' => $stats
        ]);
    }

    public function monthlyCollection(Request $request)
    {
        $tuitionId = $request->user()->id;

        // ✅ Check if request has year & month, else use previous month
        if ($request->has(['year', 'month'])) {
            $year = $request->input('year');
            $month = $request->input('month');

            // Format year_month as "F Y" (e.g. "August 2025")
            $dateObj = \Carbon\Carbon::createFromFormat('Y-m', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));
            $selectedMonth = $dateObj->format('F Y');
        } else {
            $selectedMonth = now()->subMonth()->format('F Y');
        }


        $stats =  DB::table('users')
            ->selectRaw('(
                SELECT COALESCE(SUM(fees.monthly_fees),0) 
                FROM fees 
                JOIN students ON fees.student_id = students.id 
                JOIN users ON students.user_id = users.id 
                WHERE fees.is_paid = 0 
                  AND fees.tuition_id = ? 
                  AND fees.year_month = ? 
                  AND users.status = "active" 
                  AND users.role = "student"
            ) as fees_due_this_month', [$tuitionId, $selectedMonth])

            ->selectRaw('(
                SELECT COALESCE(SUM(fees.monthly_fees),0) 
                FROM fees 
                JOIN students ON fees.student_id = students.id 
                JOIN users ON students.user_id = users.id 
                WHERE fees.is_paid = 1 
                  AND fees.tuition_id = ? 
                  AND fees.year_month = ? 
                  AND users.status = "active" 
                  AND users.role = "student"
            ) as fees_paid_this_month', [$tuitionId, $selectedMonth])
            ->first();


        return response()->json([
            'status' => 'success',
            'msg'    => 'Dashboard data fetched successfully',
            'data'   => $stats
        ]);
    }
}
