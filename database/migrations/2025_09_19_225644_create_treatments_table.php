<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade'); 
            $table->string('treatment_type');        
            $table->integer('cost');                         // 💡 cost added
            $table->string('status')->default('pending');    // 💡 status added
            $table->text('description')->nullable(); 
            $table->date('treatment_date')->nullable();      // optional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
