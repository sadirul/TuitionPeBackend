<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileService
{
    public function update(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::findOrFail($userId);

            $user->update([
                'name'    => $data['name'] ?? $user->name,
                'mobile'  => $data['mobile'] ?? $user->mobile,
                'address' => $data['address'] ?? $user->address,
                'email'   => $data['email'] ?? $user->email,
                'upi_id'   => $data['upi_id'] ?? $user->upi_id,
            ]);

            return [
                'status'  => 'success',
                'msg' => 'User updated successfully',
                'data'    => $user->fresh(),
            ];
        });
    }

    public function changePassword(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::findOrFail($userId);

            // verify old password
            if (!Hash::check($data['password'], $user->password)) {
                return [
                    'status'  => 'error',
                    'msg' => 'Current password is incorrect',
                ];
            }

            // update new password
            $user->update([
                'password' => $data['new_password'],
            ]);

            return [
                'status'  => 'success',
                'msg' => 'Password changed successfully',
            ];
        });
    }
}
