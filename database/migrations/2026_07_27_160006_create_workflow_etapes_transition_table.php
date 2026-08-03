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
        // Table commented out in schema.v2.sql - not needed for current workflow implementation
        // Schema::create('workflow_etapes_transition', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('version');
        //     $table->string('from_etape_code');
        //     $table->string('to_etape_code');
        //     $table->enum('transition_type', ['NEXT', 'RETURN', 'PARALLEL', 'MERGE', 'CANCEL', 'END'])->default('NEXT');
        //     $table->integer('order')->default(1);
        //     $table->boolean('is_active')->default(true);
        //     
        //     $table->foreign('version')->references('id')->on('workflow_versions')->onDelete('cascade');
        //     $table->foreign('from_etape_code')->references('code')->on('workflow_etapes')->onDelete('cascade');
        //     $table->foreign('to_etape_code')->references('code')->on('workflow_etapes')->onDelete('cascade');
        //     $table->unique(['version', 'from_etape_code', 'to_etape_code']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_etapes_transition');
    }
};
