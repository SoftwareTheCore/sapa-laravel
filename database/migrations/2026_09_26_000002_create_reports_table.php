<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->string('citizen_name')->nullable();
            $table->string('citizen_email')->nullable();
            $table->string('title');
            $table->string('category');
            $table->text('description');
            $table->string('location');
            $table->string('photo_path')->nullable();
            $table->string('status')->default('baru')->index();
            $table->text('staff_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
