<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();

            $table->string('qr_id', 50)->unique();

            $table->string('name')->nullable();

            $table->string('phone', 30)->nullable();

            $table->string('email')->nullable();

            $table->string('website')->nullable();

            $table->text('address')->nullable();

            $table->longText('qr_data');

            $table->string('qr_image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};