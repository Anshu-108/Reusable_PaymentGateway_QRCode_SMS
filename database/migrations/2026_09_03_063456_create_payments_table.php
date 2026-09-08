<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            $table->string('order_id')->unique();

            $table->string('gateway')->default('razorpay');

            $table->decimal('amount', 12, 2);

            $table->string('currency', 10)->default('INR');

            $table->string('status')->default('created');

            $table->string('payment_id')->nullable();

            $table->string('receipt')->nullable();

            $table->string('customer_name')->nullable();

            $table->string('customer_email')->nullable();

            $table->string('customer_phone', 20)->nullable();

            $table->text('description')->nullable();

            $table->json('gateway_response')->nullable();

            $table->timestamps();

            $table->index('payment_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};