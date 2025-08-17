<?php

namespace App\Services;

use App\Http\Requests\FeeRequest;
use App\Models\Fee;
use Exception;
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
}
