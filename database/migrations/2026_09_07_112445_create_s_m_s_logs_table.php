<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_template_id')->nullable()->constrained('sms_templates')->nullOnDelete();
            $table->string('phone_number', 20);
            $table->text('message');
            $table->string('template_id')->nullable();
            $table->string('header', 20)->nullable();
            $table->string('entity_id')->nullable();
            $table->string('provider', 50)->default('vinbox');
            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->string('gateway_message_id')->nullable();
            $table->text('gateway_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};