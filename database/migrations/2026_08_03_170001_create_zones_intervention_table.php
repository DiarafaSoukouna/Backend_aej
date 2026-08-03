<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones_intervention', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projet_id');
            $table->unsignedBigInteger('departement_id')->nullable();
            $table->text('adresse')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('projet_id')->references('id')->on('projets')->onDelete('cascade');
            $table->foreign('departement_id')->references('id')->on('departements');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones_intervention');
    }
};
