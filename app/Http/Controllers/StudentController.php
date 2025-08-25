<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateStudentClassRequest;
use App\Http\Requests\BulkUpdateStudentStatusRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;
    protected $request;

    public function __construct(StudentService $studentService, Request $request)
    {
        $this->studentService = $studentService;
        $this->request = $request;
    }

    public function store(StoreStudentRequest $request)
    {
        return $this->studentService->store($request->user()->id, $request->validated());
    }

    public function index(?string $student_uuid = null)
    {
        return $this->studentService->index($this->request->user()->id, $student_uuid);
    }

    public function update(UpdateStudentRequest $request)
    {
        return $this->studentService->update($this->request->user()->id, $request->validated());
    }

    public function changeClass(BulkUpdateStudentClassRequest $request)
    {
        return $this->studentService->changeClass($this->request->user()->id, $request->validated());
    }

    public function changeStatus(BulkUpdateStudentStatusRequest $request)
    {
        return $this->studentService->changeStatus($this->request->user()->id, $request->validated());
    }
}
