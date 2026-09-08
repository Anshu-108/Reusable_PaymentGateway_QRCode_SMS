<?php

namespace App\Services\Payment;

use App\Services\Payment\Providers\RazorpayService;

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct()
    {
        $this->gateway = new RazorpayService();
    }

    /**
     * Create Razorpay Payment Link
     */
    public function createPaymentLink(array $data)
    {
        return $this->gateway->createPaymentLink($data);
    }

    /**
     * Fetch Razorpay Payment Link
     */
    public function fetchPaymentLink(string $paymentLinkId)
    {
        return $this->gateway->fetchPaymentLink($paymentLinkId);
    }

    /**
     * Fetch Razorpay Payment
     */
    public function fetchPayment(string $paymentId)
    {
        return $this->gateway->fetchPayment($paymentId);
    }

    /**
     * Refund Razorpay Payment
     */
    public function refund(
        string $paymentId,
        ?float $amount = null
    ) {
        return $this->gateway->refund(
            $paymentId,
            $amount
        );
    }
}