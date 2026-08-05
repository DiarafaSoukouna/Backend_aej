<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_etapes_decision', function (Blueprint $table) {
            $table->id();
            $table->string('etape_code', 50);
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->text('outcomes'); // Pipe-separated codes
            
            $table->foreign('etape_code')->references('code')->on('workflow_etapes')->onDelete('cascade');
            $table->unique(['etape_code', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_etapes_decision');
    }
};
