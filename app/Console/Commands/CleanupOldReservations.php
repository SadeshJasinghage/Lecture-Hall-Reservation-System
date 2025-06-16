<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupOldReservations extends Command
{
    protected $signature = 'reservations:cleanup';
    protected $description = 'Remove reservations older than yesterday with Approved status and Requested state';

    public function handle()
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $deleted = DB::table('reservations')
            ->where('date', '<', $yesterday)
            ->where('approval_status', 'Approved')
            ->where('status', 'Requested')
            ->delete();

        $this->info("Deleted $deleted old reservations.");
    }
}

