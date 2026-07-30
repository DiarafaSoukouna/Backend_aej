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
        Schema::create('workflow_etapes_decision', function (Blueprint $table) {
            $table->id();
            $table->string('etape_code');
            $table->string('name', 150);
            $table->text('description')->nullable();
            
            $table->foreign('etape_code')->references('code')->on('workflow_etapes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_etapes_decision');
    }
};
