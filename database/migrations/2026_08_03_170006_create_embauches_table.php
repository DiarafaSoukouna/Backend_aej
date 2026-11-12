<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embauches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('type_emploi_id')->nullable();
            $table->string('poste', 200);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->foreign('promoteur_id')->references('id')->on('promoteurs')->onDelete('restrict');
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('restrict');
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('restrict');
            $table->foreign('type_emploi_id')->references('id')->on('type_emplois');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embauches');
    }
};

