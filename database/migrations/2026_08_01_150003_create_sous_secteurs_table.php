<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sous_secteurs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('secteur_id')->nullable();
            $table->string('libelle');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('secteur_id')->references('id')->on('secteurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sous_secteurs');
    }
};
