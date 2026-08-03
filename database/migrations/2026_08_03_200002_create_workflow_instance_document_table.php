<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instance_document', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_instance_id');
            $table->unsignedBigInteger('deliverable_id')->nullable();
            $table->string('file_reference', 500)->nullable();
            $table->timestamp('produced_at')->useCurrent();
            $table->unsignedBigInteger('produced_by_id')->nullable();
            
            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instance')->onDelete('cascade');
            $table->foreign('deliverable_id')->references('id')->on('workflow_etapes_deliverable');
            $table->foreign('produced_by_id')->references('id')->on('personnels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instance_document');
    }
};
