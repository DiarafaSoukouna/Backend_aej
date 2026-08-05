<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('divisionregionaleaej_id')->nullable();
            $table->unsignedBigInteger('guichetemploi_id')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('nom', 100);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('cascade');
            $table->foreign('divisionregionaleaej_id')->references('id')->on('division_regionale')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
