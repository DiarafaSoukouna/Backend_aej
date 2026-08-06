<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences_regionales', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code');
            $table->string('nom');
            $table->string('latitude', 100)->nullable();
            $table->string('longitude', 100)->nullable();
            $table->string('contact', 100)->nullable();
            $table->string('localisation', 50)->nullable();
            $table->text('adresse')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->unsignedBigInteger('chef_agence_id')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('chef_agence_id')->references('id')->on('personnels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences_regionales');
    }
};
