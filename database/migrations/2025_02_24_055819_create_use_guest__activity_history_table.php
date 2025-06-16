<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('user_guest_activity_history', function (Blueprint $table) {
            $table->id('activity_id');
            $table->string('user');
            $table->string('role');
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->timestamp('requested_date')->useCurrent();
            $table->string('hall_name');
            $table->string('course_code');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            // Status of the reservation request (e.g. "Requested" or "Cancelled")
            $table->string('status')->default('Requested');
            // Approval status set by the admin ("Approved", "Rejected", or "Pending")
            $table->string('approval_status')->default('Pending');
            // Additional columns for User and Role
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_guest_activity_history');
    }
};
