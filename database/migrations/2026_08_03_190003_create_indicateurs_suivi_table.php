<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicateurs_suivi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indicateur_id')->nullable();
            $table->string('valeur', 255);
            $table->timestamps();
            
            $table->foreign('indicateur_id')->references('id')->on('indicateurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicateurs_suivi');
    }
};
