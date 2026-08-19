<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->string('workflow_version', 50);
            $table->string('current_etape_code', 50)->nullable();
            $table->enum('statut', ['EN_COURS', 'TERMINE', 'REJETE', 'ABANDONNE'])->default('EN_COURS');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('workflow_version')->references('code')->on('workflow_versions');
            $table->foreign('current_etape_code')->references('code')->on('workflow_etapes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instance');
    }
};
