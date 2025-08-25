<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $stats = DB::table('users')
            ->selectRaw('(SELECT COUNT(*) FROM users WHERE role = "student" AND status = "active") as total_active_students')
            ->selectRaw('(SELECT COUNT(*) FROM users WHERE role = "student" AND status = "inactive") as total_inactive_students')
            ->selectRaw('(SELECT COUNT(*) FROM classes) as total_classes')
            ->selectRaw('(SELECT COUNT(*) FROM fees WHERE is_paid = 0) as total_fees_due')
            ->first();


        return response()->json([
            'status' => 'success',
            'msg' => 'Dashboard data fetched successfully',
            'data' => $stats
        ]);
    }
}
