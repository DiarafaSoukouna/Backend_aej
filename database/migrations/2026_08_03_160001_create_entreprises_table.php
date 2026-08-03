<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();
            $table->string('raison_sociale', 200);
            $table->string('sigle', 30)->nullable();
            $table->string('rccm', 50)->unique()->nullable();
            $table->string('ninea', 50)->unique()->nullable();
            $table->unsignedBigInteger('type_entreprise_id')->nullable();
            $table->text('adresse')->nullable();
            $table->string('contact', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('commune_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('type_entreprise_id')->references('id')->on('type_entreprises');
            $table->foreign('region_id')->references('id')->on('regions');
            $table->foreign('commune_id')->references('id')->on('communes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};
