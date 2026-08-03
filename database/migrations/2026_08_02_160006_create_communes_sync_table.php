<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes_sync', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique();
            $table->string('nom', 100);
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('divisionregionaleaej_id')->nullable();
            $table->unsignedBigInteger('guichetemploi_id')->nullable();
            $table->string('code', 10)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('ville_id')->references('id')->on('villes');
            $table->foreign('divisionregionaleaej_id')->references('id')->on('agences_regionales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes_sync');
    }
};
