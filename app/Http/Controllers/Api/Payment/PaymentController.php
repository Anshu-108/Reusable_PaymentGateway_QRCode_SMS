<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\PaymentTransaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create Razorpay Payment Link
     */
    public function createPaymentLink(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {

            $amount = (float) $validated['amount'];
            $amountInPaise = (int) round($amount * 100);
            $referenceId = 'PAY_' . now()->format('YmdHis') . '_' . random_int(1000, 9999);

            /*
             * Payment Link expiry
             */
            $expireBy = now()->addHours(24)->timestamp;

            /*
             * Callback URL
             */
            $callbackUrl = route('api.payment.callback');

            /*
             * Create Razorpay Payment Link
             */
            $paymentLink = $this->paymentService->createPaymentLink([
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'reference_id' => $referenceId,
                'description' => $validated['description'] ?? 'Payment',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'callback_url' => $callbackUrl,
                'expire_by' => $expireBy,
            ]);

            /*
             * Convert Razorpay response
             */
            $paymentLinkData = is_object($paymentLink) ? $paymentLink->toArray() : $paymentLink;
            $paymentLinkId = $paymentLinkData['id'] ?? null;
            $shortUrl = $paymentLinkData['short_url'] ?? null;

            /*
             * Validate Razorpay response
             */
            if (!$paymentLinkId || !$shortUrl) {
                Log::error(
                    'Razorpay Payment Link response invalid',
                    [
                        'response' => $paymentLinkData,
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to create payment link.',
                ], 500);
            }

            /*
             * Store payment in local database
             */
            $payment = Payment::create([
                'order_id' => $referenceId,
                'gateway' => 'razorpay',
                'amount' => $amount,
                'currency' => 'INR',
                'status' => 'created',
                'receipt' => $referenceId,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'description' => $validated['description'] ?? null,
                'gateway_response' => $paymentLinkData,
            ]);

            /*
             * API response
             */
            return response()->json([
                'success' => true,

                'message' =>
                    'Payment link created successfully.',

                'data' => [
                    'payment_id' => $payment->id,

                    'reference_id' =>
                        $referenceId,

                    'razorpay_payment_link_id' =>
                        $paymentLinkId,

                    'payment_link' =>
                        $shortUrl,

                    'amount' => $amount,

                    'currency' => 'INR',

                    'status' => 'created',

                    'expires_at' =>
                        $expireBy,
                ],
            ], 201);

        } catch (Throwable $e) {

            Log::error(
                'Razorpay Payment Link creation failed',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,

                'message' =>
                    'Unable to create payment. Please try again.',
            ], 500);
        }
    }


    /**
     * Razorpay Payment Link Callback
     */
    public function callback(Request $request)
    {
        $paymentLinkId =
            $request->query(
                'razorpay_payment_link_id'
            );

        $referenceId =
            $request->query(
                'razorpay_payment_link_reference_id'
            );

        $razorpayPaymentId =
            $request->query(
                'razorpay_payment_id'
            );

        $razorpaySignature =
            $request->query(
                'razorpay_signature'
            );

        /*
         * Store complete callback request
         */
        $callbackPayload =
            $request->query();

        /*
         * Find local payment
         */
        $payment = null;

        if ($referenceId) {

            $payment = Payment::where(
                'order_id',
                $referenceId
            )->first();
        }

        /*
         * Store callback immediately
         */
        $callback = PaymentCallback::create([
            'payment_id' =>
                $payment?->id,

            'event' =>
                'payment_link.callback',

            'razorpay_order_id' =>
                null,

            'razorpay_payment_id' =>
                $razorpayPaymentId,

            'payload' =>
                $callbackPayload,

            'status' =>
                'received',
        ]);

        /*
         * Validate callback parameters
         */
        if (
            !$paymentLinkId ||
            !$referenceId
        ) {

            $callback->update([
                'status' => 'failed',
            ]);

            return response()->json([
                'success' => false,

                'message' =>
                    'Invalid payment response.',
            ], 400);
        }

        try {

            /*
             * Payment record not found
             */
            if (!$payment) {

                Log::warning(
                    'Payment record not found',
                    [
                        'reference_id' =>
                            $referenceId,

                        'payment_link_id' =>
                            $paymentLinkId,
                    ]
                );

                $callback->update([
                    'status' => 'failed',
                ]);

                return response()->json([
                    'success' => false,

                    'message' =>
                        'Payment record not found.',
                ], 404);
            }

            /*
             * Make sure callback is linked
             */
            if (!$callback->payment_id) {

                $callback->update([
                    'payment_id' =>
                        $payment->id,
                ]);
            }

            /*
             * Fetch Payment Link from Razorpay
             */
            $razorpayLink =
                $this->paymentService
                    ->fetchPaymentLink(
                        $paymentLinkId
                    );

            $razorpayLinkData =
                is_object($razorpayLink)
                ? $razorpayLink->toArray()
                : $razorpayLink;

            $linkStatus =
                $razorpayLinkData['status']
                ?? null;

            /*
             * Update callback with Razorpay data
             */
            $callback->update([
                'payload' => [
                    'callback' =>
                        $callbackPayload,

                    'payment_link' =>
                        $razorpayLinkData,
                ],
            ]);

            /*
             * Payment not completed
             */
            if ($linkStatus !== 'paid') {

                $payment->update([
                    'status' => 'failed',

                    'gateway_response' =>
                        $razorpayLinkData,
                ]);

                $callback->update([
                    'status' => 'processed',
                ]);

                return response()->json([
                    'success' => false,

                    'message' =>
                        'Payment was not completed.',

                    'data' => [
                        'payment_id' =>
                            $payment->id,

                        'reference_id' =>
                            $referenceId,

                        'razorpay_payment_id' =>
                            $razorpayPaymentId,

                        'status' =>
                            $linkStatus,
                    ],
                ], 400);
            }

            /*
             * Payment ID must exist
             */
            if (!$razorpayPaymentId) {

                $callback->update([
                    'status' => 'failed',
                ]);

                return response()->json([
                    'success' => false,

                    'message' =>
                        'Payment confirmation failed.',
                ], 400);
            }

            /*
             * Fetch actual Razorpay payment
             */
            $razorpayPayment =
                $this->paymentService
                    ->fetchPayment(
                        $razorpayPaymentId
                    );

            $razorpayPaymentData =
                is_object($razorpayPayment)
                ? $razorpayPayment->toArray()
                : $razorpayPayment;

            /*
             * Update database atomically
             */
            DB::transaction(function () use ($payment, $callback, $referenceId, $paymentLinkId, $razorpayPaymentId, $razorpaySignature, $razorpayPaymentData, $razorpayLinkData) {

                /*
                 * Update payment
                 */
                $payment->update([
                    'status' => 'paid',

                    'payment_id' =>
                        $razorpayPaymentId,

                    'gateway_response' => [

                        'payment_link' =>
                            $razorpayLinkData,

                        'payment' =>
                            $razorpayPaymentData,

                        'callback' => [

                            'payment_link_id' =>
                                $paymentLinkId,

                            'reference_id' =>
                                $referenceId,

                            'payment_id' =>
                                $razorpayPaymentId,

                            'signature' =>
                                $razorpaySignature,
                        ],
                    ],
                ]);

                /*
                 * Prevent duplicate transaction
                 */
                $existingTransaction =
                    PaymentTransaction::where(
                        'razorpay_payment_id',
                        $razorpayPaymentId
                    )->first();

                if (!$existingTransaction) {

                    PaymentTransaction::create([

                        'payment_id' =>
                            $payment->id,

                        'razorpay_payment_id' =>
                            $razorpayPaymentId,

                        /*
                         * Currently storing payment link ID
                         * in this existing column.
                         */
                        'razorpay_order_id' =>
                            $paymentLinkId,

                        'razorpay_signature' =>
                            $razorpaySignature,

                        'amount' =>
                            $payment->amount,

                        'currency' =>
                            $payment->currency,

                        'method' =>
                            $razorpayPaymentData['method']
                            ?? null,

                        'bank' =>
                            $razorpayPaymentData['bank']
                            ?? null,

                        'wallet' =>
                            $razorpayPaymentData['wallet']
                            ?? null,

                        'vpa' =>
                            $razorpayPaymentData['vpa']
                            ?? null,

                        'response' =>
                            $razorpayPaymentData,

                        'status' =>
                            'success',

                        'paid_at' =>
                            now(),
                    ]);
                }

                /*
                 * Mark callback processed
                 */
                $callback->update([
                    'status' =>
                        'processed',
                ]);
            });

            /*
             * API success response
             */
            return response()->json([
                'success' => true,

                'message' =>
                    'Payment completed successfully.',

                'data' => [
                    'payment_id' =>
                        $payment->id,

                    'reference_id' =>
                        $referenceId,

                    'razorpay_payment_id' =>
                        $razorpayPaymentId,

                    'razorpay_payment_link_id' =>
                        $paymentLinkId,

                    'razorpay_signature' => $razorpaySignature,

                    'amount' =>
                        $payment->amount,

                    'currency' =>
                        $payment->currency,

                    'status' =>
                        'paid',
                ],
            ]);

        } catch (Throwable $e) {

            Log::error(
                'Razorpay API callback processing failed',
                [
                    'payment_link_id' =>
                        $paymentLinkId,

                    'reference_id' =>
                        $referenceId,

                    'payment_id' =>
                        $razorpayPaymentId,

                    'message' =>
                        $e->getMessage(),
                ]
            );

            $callback->update([
                'status' =>
                    'failed',

                'payload' =>
                    array_merge(
                        $callback->payload ?? [],
                        [
                            'error' =>
                                $e->getMessage(),
                        ]
                    ),
            ]);

            return response()->json([
                'success' => false,

                'message' =>
                    'Payment verification failed.',
            ], 500);
        }
    }


    /**
     * Get payment details
     */
    public function show($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {

            return response()->json([
                'success' => false,

                'message' =>
                    'Payment not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,

            'data' => [
                'id' =>
                    $payment->id,

                'reference_id' =>
                    $payment->order_id,

                'amount' =>
                    $payment->amount,

                'currency' =>
                    $payment->currency,

                'status' =>
                    $payment->status,

                'payment_id' =>
                    $payment->payment_id,

                'customer_name' =>
                    $payment->customer_name,

                'customer_email' =>
                    $payment->customer_email,

                'customer_phone' =>
                    $payment->customer_phone,

                'created_at' =>
                    $payment->created_at,
            ],
        ]);
    }
}