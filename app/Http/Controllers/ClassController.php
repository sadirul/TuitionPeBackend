<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Services\ClassService;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    protected $classService;
    protected $request;

    public function __construct(ClassService $classService, Request $request)
    {
        $this->classService = $classService;
        $this->request = $request;
    }

    public function store(StoreClassRequest $request)
    {
        return $this->classService->store($request->user()->id, $request->validated());
    }

    public function index()
    {
        return $this->classService->index($this->request->user()->id);
    }

    public function update(string $uuid, UpdateClassRequest $request)
    {
        return $this->classService->update($this->request->user()->id, $uuid, $request->validated());
    }
}
