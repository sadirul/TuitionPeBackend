<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{

    protected $profileService;
    protected $request;

    public function __construct(ProfileService $profileService, Request $request)
    {
        $this->profileService = $profileService;
        $this->request = $request;
    }

    public function update(UpdateProfileRequest $request)
    {
        return $this->profileService->update($this->request->user()->id, $request->validated());
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->profileService->changePassword($this->request->user()->id, $request->validated());
    }
}
