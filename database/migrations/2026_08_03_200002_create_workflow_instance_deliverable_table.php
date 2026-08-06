<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instance_deliverable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_instance_id');
            $table->string('deliverable_code', 50)->nullable();
            $table->text('file_path')->nullable();
            $table->string('file_name', 255)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_type', 100)->nullable();
            $table->timestamp('produced_at')->useCurrent();
            $table->unsignedBigInteger('produced_by_id')->nullable();
            
            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instance')->onDelete('cascade');
            $table->foreign('deliverable_code')->references('code')->on('workflow_deliverables');
            $table->foreign('produced_by_id')->references('id')->on('personnels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instance_deliverable');
    }
};
