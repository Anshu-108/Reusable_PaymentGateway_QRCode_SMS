<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create Razorpay Payment Link
     */
    public function createPaymentLink(array $data);

    /**
     * Fetch Razorpay Payment Link
     */
    public function fetchPaymentLink(string $paymentLinkId);

    /**
     * Fetch Razorpay Payment
     */
    public function fetchPayment(string $paymentId);

    /**
     * Refund Payment
     */
    public function refund(
        string $paymentId,
        ?float $amount = null
    );
}