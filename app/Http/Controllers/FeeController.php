<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeUpdateRequest;
use App\Services\FeeService;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    protected $feeService;
    protected $request;

    public function __construct(FeeService $feeService, Request $request)
    {
        $this->feeService = $feeService;
        $this->request = $request;
    }

    public function update(FeeUpdateRequest $request)
    {
        return $this->feeService->update($this->request->user()->id, $request->validated());
    }
}
