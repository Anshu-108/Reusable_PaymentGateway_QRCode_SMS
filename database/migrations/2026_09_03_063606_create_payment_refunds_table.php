<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_refunds', function (Blueprint $table) {

            $table->id();

            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();

            $table->string('refund_id')->nullable();

            $table->decimal('amount', 12, 2);

            $table->string('currency', 10)->default('INR');

            $table->string('status')->default('created');

            $table->string('reason')->nullable();

            $table->json('response')->nullable();

            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index('refund_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};