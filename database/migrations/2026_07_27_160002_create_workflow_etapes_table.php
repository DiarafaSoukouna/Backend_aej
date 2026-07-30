<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_etapes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('version');
            $table->string('parent_etape_code', 30)->nullable();
            $table->string('code', 30);
            $table->string('name', 200);
            $table->enum('impact', ['EN_SOUMISSION', 'EN_COURS', 'EN_ANALYSE', 'EN_FORMATION', 'EN_FINANCEMENT', 'EN_DECAISSEMENT', 'EN_SUIVI', 'EN_REMBOURSEMENT', 'EN_EVALUATION', 'TERMINE'])->nullable();
            $table->enum('statut', ['OUI', 'NON'])->default('NON');
            $table->text('description')->nullable();
            $table->integer('sequence_order');
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->default(now());
            $table->date('valid_to')->nullable();
            $table->timestamps();
            
            $table->foreign('version')->references('version')->on('workflow_versions')->onDelete('cascade');
            $table->foreign('parent_etape_code')->references('code')->on('workflow_etapes')->onDelete('cascade');
            $table->unique(['version', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_etapes');
    }
};
