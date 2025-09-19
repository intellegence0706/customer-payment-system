<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->json('parameters')->nullable();
            $table->foreignId('generated_by')->constrained('users');
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
            
            $table->index(['type', 'created_at']);
            $table->index('generated_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
