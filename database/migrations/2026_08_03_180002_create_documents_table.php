<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id');
            $table->string('type_document', 100);
            $table->string('fichier', 255);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
