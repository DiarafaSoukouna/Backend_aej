<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositifs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->unsignedBigInteger('projet_id')->unique()->nullable();
            $table->string('intitule', 200);
            $table->decimal('budget_alloue', 15, 2);
            $table->integer('nbre_emplois_prevu')->default(0);
            $table->integer('nbre_beneficiaire_prevu')->default(0);
            $table->integer('nbre_micro_projet_prevu')->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            
            $table->foreign('projet_id')->references('id')->on('projets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositifs');
    }
};
