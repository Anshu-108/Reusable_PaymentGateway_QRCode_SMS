<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_callbacks', function (Blueprint $table) {

            $table->id();

            $table->string('event');

            $table->string('razorpay_order_id')->nullable();

            $table->string('razorpay_payment_id')->nullable();

            $table->json('payload');

            $table->string('status')->default('received');

            $table->timestamps();

            $table->index('event');

            $table->index('razorpay_order_id');

            $table->index('razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_callbacks');
    }
};