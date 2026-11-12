<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots_importation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('nom_promoteur', 100)->nullable();
            $table->string('prenom_promoteur', 100)->nullable();
            $table->decimal('montant_sollicite', 15, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots_importation');
    }
};

