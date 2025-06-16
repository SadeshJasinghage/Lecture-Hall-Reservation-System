<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('reservation_id');
            $table->unsignedBigInteger('hall_id');
            $table->foreign('hall_id')->references('hall_id')->on('department_mathematics_lecture_halls')->onDelete('cascade');
            $table->string('hall_name'); // Store hall name for convenience
            $table->string('user_name'); // Name of the user reserving
            $table->string('role'); // Role (e.g., Student, Faculty)
            $table->string('course_code');
            $table->date('date'); // Date of reservation
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps(); // Created_at & Updated_at

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
