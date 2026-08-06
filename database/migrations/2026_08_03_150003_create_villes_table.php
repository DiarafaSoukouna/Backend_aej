<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('departement_id')->nullable();
            $table->string('code', 10)->nullable();
            $table->string('nom', 100);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('departement_id')->references('id')->on('departements');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};
