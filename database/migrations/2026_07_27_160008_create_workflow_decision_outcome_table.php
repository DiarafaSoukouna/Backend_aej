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
        Schema::create('workflow_decision_outcome', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('decision_id');
            $table->string('code', 30);
            $table->string('label', 150);
            $table->string('next_etape_code')->nullable();
            
            $table->foreign('decision_id')->references('id')->on('workflow_etapes_decision')->onDelete('cascade');
            $table->foreign('next_etape_code')->references('code')->on('workflow_etapes')->onDelete('set null');
            $table->unique(['decision_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_decision_outcome');
    }
};
