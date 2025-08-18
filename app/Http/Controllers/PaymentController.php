<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Models\Payment;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function createOrder(CreatePaymentRequest $request)
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $order = $api->order->create([
            'receipt'   => 'order_rcptid_' . time(),
            'amount'    => $request->amount * 100, // Razorpay expects paise
            'currency'  => 'INR'
        ]);

        // Save order in DB with tuition_id
        Payment::create([
            'tuition_id' => $request->user()->id, // logged-in tuition user
            'order_id'   => $order['id'],
            'amount'     => $request->amount * 100,
            'status'     => 'created',
        ]);

        return response()->json([
            'status'   => 'success',
            'order_id' => $order['id'],
            'amount'   => $request->amount,
            'key'      => config('services.razorpay.key'),
        ]);
    }

    // Verify Razorpay Payment
    public function verifyPayment(VerifyPaymentRequest $request)
    {
        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        try {
            $api->utility->verifyPaymentSignature($request->only([
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature'
            ]));

            $existingPayment = Payment::where('payment_id', $request->razorpay_payment_id)->first();
            if ($existingPayment) {
                return response()->json([
                    'status'  => 'error',
                    'msg' => 'This payment has already been verified',
                ], 409);
            }

            $payment = Payment::where('order_id', $request->razorpay_order_id)->first();

            if ($payment) {
                $payment->update([
                    'payment_id' => $request->razorpay_payment_id,
                    'signature'  => $request->razorpay_signature,
                    'status'     => 'success',
                ]);
            } else {
                return response()->json([
                    'status'  => 'error',
                    'msg' => 'Payment record not found for this order',
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'msg' => 'Payment verified successfully',
                'data'    => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'msg' => 'Payment verification failed',
                'error'   => $e->getMessage()
            ], 400);
        }
    }
}
