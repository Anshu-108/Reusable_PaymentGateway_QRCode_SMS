<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();

            $table->string('razorpay_payment_id')->nullable();

            $table->string('razorpay_order_id');

            $table->string('razorpay_signature')->nullable();

            $table->decimal('amount', 12, 2);

            $table->string('currency', 10)->default('INR');

            $table->string('status')->default('created');

            $table->string('method')->nullable();

            $table->string('bank')->nullable();

            $table->string('wallet')->nullable();

            $table->string('vpa')->nullable();

            $table->json('response')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('razorpay_payment_id');

            $table->index('razorpay_order_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};