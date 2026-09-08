<?php

namespace App\Http\Controllers\Payment;

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
     * Payment Page
     */
    public function index()
    {
        return view('payment.index');
    }

    /**
     * Create Razorpay Payment Link
     */
    public function createPaymentLink(Request $request)
    {
        $validated = $request->validate([
            'amount'            => ['required', 'numeric', 'min:1', 'max:1000000'],
            'customer_name'     => ['required','string', 'max:255'],
            'customer_email'    => ['required','email', 'max:255'],
            'customer_phone'    => ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'],
            'description'       => ['nullable', 'string', 'max:500'],
        ]);

        try {

            $amount = (float) $validated['amount'];
            $amountInPaise = (int) round($amount * 100);
            $referenceId = 'PAY_' . now()->format('YmdHis') . '_' . random_int(1000, 9999);
            $expireBy = now()->addHours(24)->timestamp;


            /*
            |--------------------------------------------------------------------------
            | Callback URL
            |--------------------------------------------------------------------------
            */

            $callbackUrl = route('payment.callback');


            /*
            |--------------------------------------------------------------------------
            | Create Razorpay Payment Link
            |--------------------------------------------------------------------------
            */

            $paymentLink = $this->paymentService->createPaymentLink([
                'amount'            => $amountInPaise,
                'currency'          => 'INR',
                'reference_id'      => $referenceId,
                'description'       => $validated['description'] ?? 'Payment',
                'customer_name'     => $validated['customer_name'],
                'customer_email'    => $validated['customer_email'],
                'customer_phone'    => $validated['customer_phone'],
                'callback_url'      => $callbackUrl,
                'expire_by'         => $expireBy,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Convert Razorpay Response
            |--------------------------------------------------------------------------
            */

            $paymentLinkData = is_object($paymentLink)
                ? $paymentLink->toArray()
                : $paymentLink;


            /*
            |--------------------------------------------------------------------------
            | Store Payment
            |--------------------------------------------------------------------------
            */

            Payment::create([
                'order_id'          => $referenceId,
                'gateway'           => 'razorpay',
                'amount'            => $amount,
                'currency'          => 'INR',
                'status'            => 'created',
                'receipt'           => $referenceId,
                'customer_name'     => $validated['customer_name'],
                'customer_email'    => $validated['customer_email'],
                'customer_phone'    => $validated['customer_phone'],
                'description'       => $validated['description'] ?? null,
                'gateway_response'  => $paymentLinkData,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Redirect Customer To Razorpay
            |--------------------------------------------------------------------------
            */

            $shortUrl = $paymentLinkData['short_url'] ?? null;

            if (!$shortUrl) {

                Log::error(
                    'Razorpay Payment Link URL missing',
                    [
                        'response' => $paymentLinkData,
                    ]
                );

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Unable to create payment link.'
                    );
            }


            return redirect()->away($shortUrl);


        } catch (Throwable $e) {

            Log::error(
                'Razorpay Payment Link creation failed',
                [
                    'message' => $e->getMessage(),

                    'trace' => $e->getTraceAsString(),
                ]
            );


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create payment. Please try again.'
                );
        }
    }


    /**
     * Razorpay Callback
     */
    // public function callback(Request $request)
    // {
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Get Razorpay Callback Data
    //     |--------------------------------------------------------------------------
    //     */

    //     $paymentLinkId = $request->query('razorpay_payment_link_id');
    //     $referenceId = $request->query('razorpay_payment_link_reference_id');
    //     $razorpayPaymentId = $request->query('razorpay_payment_id');
    //     $razorpaySignature = $request->query('razorpay_signature');

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Basic Validation
    //     |--------------------------------------------------------------------------
    //     */

    //     if (
    //         !$paymentLinkId ||
    //         !$referenceId
    //     ) {

    //         return redirect()->route('payment.failed')->with(
    //                 'error',
    //                 'Invalid payment response.'
    //             );
    //     }

    //     try {
    //         /*
    //         |--------------------------------------------------------------------------
    //         | Find Local Payment
    //         |--------------------------------------------------------------------------
    //         */

    //         $payment = Payment::where('order_id', $referenceId)->first();

    //         if (!$payment) {
    //             Log::warning(
    //                 'Payment record not found',
    //                 [
    //                     'reference_id'      => $referenceId,
    //                     'payment_link_id'   => $paymentLinkId,
    //                 ]
    //             );

    //             return redirect()
    //                 ->route('payment.failed')
    //                 ->with(
    //                     'error',
    //                     'Payment record not found.'
    //                 );
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Fetch Payment Link From Razorpay
    //         |--------------------------------------------------------------------------
    //         */
    //         $razorpayLink = $this->paymentService->fetchPaymentLink($paymentLinkId);
    //         $razorpayLinkData = is_object($razorpayLink) ? $razorpayLink->toArray() : $razorpayLink;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Check Payment Link Status
    //         |--------------------------------------------------------------------------
    //         */
    //         $linkStatus = $razorpayLinkData['status'] ?? null;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Payment Not Successful
    //         |--------------------------------------------------------------------------
    //         */
    //         if ($linkStatus !== 'paid') {
    //             $payment->update([
    //                 'status'            => 'failed',
    //                 'gateway_response'  => $razorpayLinkData,
    //             ]);

    //             return redirect()->route(
    //                 'payment.failed',
    //                 [
    //                     'order_id'      => $referenceId,
    //                     'payment_id'    => $razorpayPaymentId,
    //                     'message'       => 'Payment was not completed.',
    //                 ]
    //             );
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Payment ID Required
    //         |--------------------------------------------------------------------------
    //         */

    //         if (!$razorpayPaymentId) {

    //             Log::warning(
    //                 'Payment marked paid but payment ID missing',
    //                 [
    //                     'payment_link_id'   => $paymentLinkId,
    //                     'reference_id'      => $referenceId,
    //                 ]
    //             );

    //             return redirect()
    //                 ->route('payment.failed')
    //                 ->with(
    //                     'error',
    //                     'Payment confirmation failed.'
    //                 );
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Fetch Actual Razorpay Payment
    //         |--------------------------------------------------------------------------
    //         */

    //         $razorpayPayment =
    //             $this->paymentService
    //                 ->fetchPayment(
    //                     $razorpayPaymentId
    //                 );

    //         $razorpayPaymentData =
    //             is_object($razorpayPayment)
    //                 ? $razorpayPayment->toArray()
    //                 : $razorpayPayment;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Transaction
    //         |--------------------------------------------------------------------------
    //         */

    //         DB::transaction(function () use (
    //             $payment,
    //             $referenceId,
    //             $paymentLinkId,
    //             $razorpayPaymentId,
    //             $razorpaySignature,
    //             $razorpayPaymentData,
    //             $razorpayLinkData
    //         ) {

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Update Payment
    //             |--------------------------------------------------------------------------
    //             */

    //             $payment->update([
    //                 'status'            => 'paid',
    //                 'payment_id'        => $razorpayPaymentId,
    //                 'gateway_response'  => [
    //                     'payment_link'      => $razorpayLinkData,
    //                     'payment'           => $razorpayPaymentData,
    //                     'callback'          => [
    //                         'payment_link_id'   => $paymentLinkId,
    //                         'reference_id'      => $referenceId,
    //                         'payment_id'        => $razorpayPaymentId,
    //                         'signature'         => $razorpaySignature,
    //                     ],
    //                 ],
    //             ]);


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Prevent Duplicate Transaction
    //             |--------------------------------------------------------------------------
    //             */

    //             $existingTransaction = PaymentTransaction::where('razorpay_payment_id', $razorpayPaymentId)->first();

    //             if (!$existingTransaction) {
    //                 PaymentTransaction::create([
    //                     'payment_id'            => $payment->id,
    //                     'razorpay_payment_id'   => $razorpayPaymentId,

    //                     /*
    //                      * Your existing database column is named
    //                      * razorpay_order_id.
    //                      *
    //                      * Payment Links don't use a normal
    //                      * Razorpay Orders API order ID here.
    //                      * We therefore store the Payment Link ID.
    //                      */
    //                     'razorpay_order_id'     => $paymentLinkId,
    //                     'razorpay_signature'    => $razorpaySignature,
    //                     'amount'                => $payment->amount,
    //                     'currency'              => $payment->currency,
    //                     'method'                => $razorpayPaymentData['method'] ?? null,
    //                     'bank'                  => $razorpayPaymentData['bank'] ?? null,
    //                     'wallet'                => $razorpayPaymentData['wallet'] ?? null,
    //                     'vpa'                   => $razorpayPaymentData['vpa'] ?? null,
    //                     'response'              => $razorpayPaymentData,
    //                     'status'                => 'success',
    //                     'paid_at'               => now(),
    //                 ]);
    //             }
    //         });


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Success
    //         |--------------------------------------------------------------------------
    //         */

    //         return redirect()
    //             ->route(
    //                 'payment.success',
    //                 [
    //                     'payment_id'    => $razorpayPaymentId,
    //                     'order_id'      => $referenceId,
    //                 ]
    //             );

    //     } catch (Throwable $e) {
    //         Log::error(
    //             'Razorpay callback processing failed',
    //             [
    //                 'payment_link_id'   => $paymentLinkId,
    //                 'reference_id'      => $referenceId,
    //                 'payment_id'        => $razorpayPaymentId,
    //                 'message'           => $e->getMessage(),
    //                 'trace'             => $e->getTraceAsString(),
    //             ]
    //         );


    //         return redirect()
    //             ->route(
    //                 'payment.failed',
    //                 [
    //                     'payment_id'    => $razorpayPaymentId,
    //                     'order_id'      => $referenceId,
    //                     'message'       => 'Payment verification failed.',
    //                 ]
    //             );
    //     }
    // }



    public function callback(Request $request)
    {
        
        $paymentLinkId = $request->query('razorpay_payment_link_id');
        $referenceId = $request->query('razorpay_payment_link_reference_id');
        $razorpayPaymentId = $request->query('razorpay_payment_id');
        $razorpaySignature = $request->query('razorpay_signature');

        /*
        |--------------------------------------------------------------------------
        | Complete Callback Payload
        |--------------------------------------------------------------------------
        */

        $callbackPayload = $request->query();

        /*
        |--------------------------------------------------------------------------
        | Find Local Payment
        |--------------------------------------------------------------------------
        */

        $payment = null;
        if ($referenceId) {
            $payment = Payment::where('order_id', $referenceId)->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Store Callback Immediately
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Store the callback BEFORE processing it.
        |
        */

        $callback = PaymentCallback::create([
            'payment_id'            => $payment?->id,
            'event'                 => 'payment_link.callback',
            'razorpay_order_id'     => null,
            'razorpay_payment_id'   => $razorpayPaymentId,
            'payload'               => $callbackPayload,
            'status'                => 'received',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Callback
        |--------------------------------------------------------------------------
        */

        if (
            !$paymentLinkId ||
            !$referenceId
        ) {
            $callback->update(['status' => 'failed']);
            return redirect()->route('payment.failed')->with('error', 'Invalid payment response.');
        }


        try {
            /*
            |--------------------------------------------------------------------------
            | Payment Record Check
            |--------------------------------------------------------------------------
            */

            if (!$payment) {
                Log::warning(
                    'Payment record not found',
                    [
                        'reference_id'      => $referenceId,
                        'payment_link_id'   => $paymentLinkId,
                    ]
                );

                $callback->update([
                    'status' => 'failed',
                ]);

                return redirect()->route('payment.failed')->with('error', 'Payment record not found.');
            }

            /*
            |--------------------------------------------------------------------------
            | Connect Callback With Payment
            |--------------------------------------------------------------------------
            */

            if (!$callback->payment_id) {
                $callback->update([
                    'payment_id' => $payment->id,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Fetch Payment Link From Razorpay
            |--------------------------------------------------------------------------
            */

            $razorpayLink = $this->paymentService->fetchPaymentLink($paymentLinkId);
            $razorpayLinkData = is_object($razorpayLink) ? $razorpayLink->toArray() : $razorpayLink;

            /*
            |--------------------------------------------------------------------------
            | Get Payment Link Status
            |--------------------------------------------------------------------------
            */

            $linkStatus = $razorpayLinkData['status'] ?? null;

            /*
            |--------------------------------------------------------------------------
            | Update Callback With Complete Razorpay Response
            |--------------------------------------------------------------------------
            */

            $callback->update([
                'payload' => [
                    'callback'      => $callbackPayload,
                    'payment_link'  => $razorpayLinkData
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Payment Not Paid
            |--------------------------------------------------------------------------
            */

            if ($linkStatus !== 'paid') {
                $payment->update([
                    'status'            => 'failed',
                    'gateway_response'  => $razorpayLinkData,
                ]);

                $callback->update([
                    'status' => 'processed',
                ]);

                return redirect()->route('payment.failed',
                    [
                        'order_id'      => $referenceId,
                        'payment_id'    => $razorpayPaymentId,
                        'message'       => 'Payment was not completed.',
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Payment ID Required
            |--------------------------------------------------------------------------
            */

            if (!$razorpayPaymentId) {
                Log::warning('Payment marked paid but payment ID missing',
                    [
                        'payment_link_id'   => $paymentLinkId,
                        'reference_id'      => $referenceId,
                    ]
                );

                $callback->update([
                    'status' => 'failed',
                ]);

                return redirect()->route('payment.failed')->with(
                        'error',
                        'Payment confirmation failed.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Fetch Actual Razorpay Payment
            |--------------------------------------------------------------------------
            */

            $razorpayPayment = $this->paymentService->fetchPayment($razorpayPaymentId);
            $razorpayPaymentData = is_object($razorpayPayment) ? $razorpayPayment->toArray() : $razorpayPayment;


            /*
            |--------------------------------------------------------------------------
            | Database Transaction
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $payment,
                $callback,
                $referenceId,
                $paymentLinkId,
                $razorpayPaymentId,
                $razorpaySignature,
                $razorpayPaymentData,
                $razorpayLinkData
            ) {

                /*
                |--------------------------------------------------------------------------
                | Update Payment
                |--------------------------------------------------------------------------
                */

                $payment->update([
                    'status'            => 'paid',
                    'payment_id'        => $razorpayPaymentId,
                    'gateway_response'  => [
                        'payment_link'  => $razorpayLinkData,
                        'payment'       => $razorpayPaymentData,
                        'callback'      => [
                            'payment_link_id'   => $paymentLinkId,
                            'reference_id'      => $referenceId,
                            'payment_id'        => $razorpayPaymentId,
                            'signature'         => $razorpaySignature,
                        ],
                    ],
                ]);


                /*
                |--------------------------------------------------------------------------
                | Create Payment Transaction
                |--------------------------------------------------------------------------
                */

                $existingTransaction = PaymentTransaction::where( 'razorpay_payment_id', $razorpayPaymentId)->first();
                if (!$existingTransaction) {
                    PaymentTransaction::create([
                        'payment_id' => $payment->id,
                        'razorpay_payment_id' => $razorpayPaymentId,

                        /*
                         * Payment Link ID is stored here
                         * because your existing column is
                         * razorpay_order_id.
                         */
                        'razorpay_order_id'     => $paymentLinkId,
                        'razorpay_signature'    => $razorpaySignature,
                        'amount'                => $payment->amount,
                        'currency'              => $payment->currency,
                        'method'                => $razorpayPaymentData['method'] ?? null,
                        'bank'                  => $razorpayPaymentData['bank'] ?? null,
                        'wallet'                => $razorpayPaymentData['wallet'] ?? null,
                        'vpa'                   => $razorpayPaymentData['vpa'] ?? null,
                        'response'              => $razorpayPaymentData,
                        'status'                => 'success',
                        'paid_at'               => now(),
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Mark Callback Processed
                |--------------------------------------------------------------------------
                */

                $callback->update([
                    'status' => 'processed',
                ]);
            });


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return redirect()->route('payment.success',
                    [
                        'payment_id'    => $razorpayPaymentId,
                        'order_id'      => $referenceId,
                    ]
                );

        } catch (Throwable $e) {

            Log::error('Razorpay callback processing failed',
                [
                    'payment_link_id'   => $paymentLinkId,
                    'reference_id'      => $referenceId,
                    'payment_id'        => $razorpayPaymentId,
                    'message'           => $e->getMessage(),
                    'trace'             => $e->getTraceAsString(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Mark Callback Failed
            |--------------------------------------------------------------------------
            */

            $callback->update([
                'status' => 'failed',
                'payload' => array_merge(
                    $callback->payload ?? [],
                    [
                        'error' => $e->getMessage(),
                    ]
                ),
            ]);


            return redirect()->route( 'payment.failed',
                    [
                        'payment_id'    => $razorpayPaymentId,
                        'order_id'      => $referenceId,
                        'message'       => 'Payment verification failed.',
                    ]
                );
        }
    }

    /**
     * Payment Success Page
     */
    public function success(Request $request)
    {
        return view(
            'payment.success',
            [
                'paymentId' => $request->query('payment_id'),
                'orderId'   => $request->query('order_id'),
            ]
        );
    }


    /**
     * Payment Failed Page
     */
    public function failed(Request $request)
    {
        return view(
            'payment.failed',
            [
                'paymentId' => $request->query('payment_id'),
                'orderId'   => $request->query('order_id'),
                'message'   => $request->query('message') ?? session('error'),
            ]
        );
    }
}