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
        Schema::create('department_mathematics_lecture_halls', function (Blueprint $table) {
            $table->id('hall_id'); // Auto-incrementing primary key
            $table->string('hall_name')->unique();
            $table->string('block'); // Block name or number
            $table->integer('seats')->default(0);
            $table->integer('projectors')->nullable(); // projectors
            $table->string('ac')->nullable(); // Add text 
            $table->integer('num_of_computers')->nullable(); // number of computers
            $table->string('status')->default('Available'); // Default hall status
            $table->date('date')->nullable(); // Date of reservation
            $table->timestamps(); // Created_at & Updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_mathematics_lecture_halls');
    }
};
