<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\RenewTransaction;
use App\Models\User;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Illuminate\Support\Str;


class PaymentController extends Controller
{
    public function createOrder(CreatePaymentRequest $request)
    {
        $user = $request->user();
        $plan = Plan::where('uuid', $request->plan_uuid)->first();
        // if (!$plan) {
        //     return response()->json([
        //         'status' => 'error',
        //         'msg' => 'Invalid plan',
        //     ], 404);
        // }
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $amountPaise = (int) round(((float) $plan->amount) * 100);
        $receipt = 'rcpt_' . Str::random(10);
        $currency = 'INR';


        $order = $api->order->create([
            'receipt'   => $receipt,
            'amount'    => $amountPaise, // Razorpay expects paise
            'currency'  => $currency
        ]);


        // Save order in DB with tuition_id
        RenewTransaction::create([
            'tuition_id' => $user->id, // logged-in tuition user
            'razorpay_order_id'   => $order['id'],
            'amount'     => $amountPaise,
            'status'     => 'created',
            'currency'  => $currency,
            'receipt'   => $receipt,
            'description' => $plan->description,
            'months' => $plan->months,
        ]);

        return response()->json([
            'status'   => 'success',
            'msg'   => 'Order created successfully',
            'order_id' => $order['id'],
            'amount'   => $amountPaise,
        ]);
    }

    // Verify Razorpay Payment
    public function verifyPayment(VerifyPaymentRequest $request)
    {
        $user = $request->user();
        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        try {
            $payment = $api->payment->fetch($request->razorpay_payment_id);
            $api->utility->verifyPaymentSignature($request->only([
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature'
            ]));

            $existingPayment = RenewTransaction::where('razorpay_payment_id', $request->razorpay_payment_id)->first();
            if ($existingPayment && $existingPayment->status === 'captured') {
                return response()->json([
                    'status'  => 'error',
                    'msg' => 'This payment has already been verified',
                ], 409);
            }

            $ordersfetch = RenewTransaction::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if ($ordersfetch) {                
                // **Calculate new expiry date**
                $tuitionExpiry = Carbon::parse($user->expiry_datetime);
                $baseDate = $tuitionExpiry->isPast() ? now() : $tuitionExpiry;
                $futureDate = $baseDate->addMonths((int)$ordersfetch->months);

                RenewTransaction::where('razorpay_order_id', $payment->order_id)
                    ->where('tuition_id', $user->id)
                    ->update([
                        'status' => $payment->status,
                        'razorpay_payment_id' => $payment->id,
                        'expiry_date' => $futureDate,
                        'razorpay_signature' => $request->razorpay_signature,
                        'json_response' => json_encode((array)$payment),
                    ]);
                User::find($user->id)->update([
                    'expiry_datetime' => $futureDate
                ]);

                return response()->json([
                    'status'  => 'success',
                    'msg' => 'Thank you for your payment. Your transaction was successful.',
                    'transaction_id' => $payment->id,
                ]);
            } else {
                return response()->json([
                    'status'  => 'error',
                    'msg' => 'Payment record not found for this order',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'msg' => 'Payment verification failed',
                'error'   => $e->getMessage()
            ], 400);
        }
    }
}
