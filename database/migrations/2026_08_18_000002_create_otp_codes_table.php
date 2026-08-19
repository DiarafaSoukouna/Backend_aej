<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
            $table->string('code', 255);
            $table->enum('mode', ['MAIL', 'WHATSAPP'])->default('MAIL');
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();
            $table->index(['personnel_id', 'expires_at']);
            $table->index('code');
            $table->index('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};