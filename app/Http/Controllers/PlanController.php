<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index() {
        $plans = Plan::all();

        return response()->json([
            'status' => 'success',
            'msg' => 'Plans fetched successfully',
            'data' => $plans
        ]);
    }
}
