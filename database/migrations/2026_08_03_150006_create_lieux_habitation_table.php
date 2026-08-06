<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieux_habitation', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('nom', 150);
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('ville_id')->references('id')->on('villes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieux_habitation');
    }
};
