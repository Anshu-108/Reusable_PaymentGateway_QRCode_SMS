<?php

namespace App\Services\Payment\Providers;

use App\Services\Payment\PaymentGatewayInterface;
use Razorpay\Api\Api;

class RazorpayService implements PaymentGatewayInterface
{
    protected Api $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('razorpay.key_id'),
            config('razorpay.key_secret')
        );
    }

    /**
     * Create Razorpay Payment Link
     */
    public function createPaymentLink(array $data)
    {
        return $this->razorpay->paymentLink->create([
            'amount'            => $data['amount'],
            'currency'          => $data['currency'] ?? 'INR',
            'accept_partial'    => false,
            'reference_id'      => $data['reference_id'],
            'description'       => $data['description'] ?? 'Payment',
            'customer'          => [
                'name'      => $data['customer_name'],
                'email'     => $data['customer_email'],
                'contact'   => $data['customer_phone'],
            ],
            'callback_url'      => $data['callback_url'],
            'callback_method'   => 'get',
            'expire_by'         => $data['expire_by'],
        ]);
    }

    /**
     * Fetch Razorpay Payment Link
     */
    public function fetchPaymentLink(string $paymentLinkId)
    {
        return $this->razorpay->paymentLink->fetch($paymentLinkId);
    }

    /**
     * Fetch Razorpay Payment
     */
    public function fetchPayment(string $paymentId)
    {
        return $this->razorpay->payment->fetch($paymentId);
    }

    /**
     * Refund Razorpay Payment
     */
    public function refund(string $paymentId, ?float $amount = null)
    {
        $data = [];

        if ($amount !== null) {
            $data['amount'] = (int) round($amount * 100);
        }

        return $this->razorpay
            ->payment
            ->fetch($paymentId)
            ->refund($data);
    }
}