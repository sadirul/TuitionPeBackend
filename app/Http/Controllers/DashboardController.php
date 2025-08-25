<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $tuitionId = $request->user()->id;

        $stats = DB::table('users')
            ->selectRaw('(SELECT COUNT(*) FROM users WHERE role = "student" AND status = "active" AND tuition_id = ?) as total_active_students', [$tuitionId])
            ->selectRaw('(SELECT COUNT(*) FROM users WHERE role = "student" AND status = "inactive" AND tuition_id = ?) as total_inactive_students', [$tuitionId])
            ->selectRaw('(SELECT COUNT(*) FROM classes WHERE tuition_id = ?) as total_classes', [$tuitionId])
            ->selectRaw('(SELECT COUNT(*) FROM fees WHERE is_paid = 0 AND tuition_id = ?) as total_fees_due', [$tuitionId])
            ->first();

        return response()->json([
            'status' => 'success',
            'msg'    => 'Dashboard data fetched successfully',
            'data'   => $stats
        ]);
    }
}
