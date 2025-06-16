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
        Schema::table('user_guest_activity_history', function (Blueprint $table) {
            $table->unsignedBigInteger('hall_id')->nullable()->after('reservation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_guest_activity_history', function (Blueprint $table) {
            $table->dropColumn('hall_id');
        });
    }
};
