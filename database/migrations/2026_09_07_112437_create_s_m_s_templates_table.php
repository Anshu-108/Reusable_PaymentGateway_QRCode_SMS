<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_id')->unique();
            $table->string('template_name');
            $table->string('header', 20);
            $table->string('entity_id')->nullable();
            $table->text('template_message');
            $table->string('provider', 50)->default('vinbox');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};