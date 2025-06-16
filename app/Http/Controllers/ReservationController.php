<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

// This separate controller for filtering available halls

class ReservationController extends Controller
{

    public function getReservations($hallId)
    {
        $reservations = DB::table('reservations')
            ->where('hall_id', $hallId)
            ->where('approval_status', 'Approved')
            ->where('status', 'Requested')
            ->select('hall_name', 'date', 'start_time', 'end_time', 'course_code')
            ->get();

        return response()->json($reservations);
    }

    public function getReservationsByDate($hallId, $date)
    {
        $reservations = DB::table('reservations')
            ->where('hall_id', $hallId)
            ->where('date', $date)
            ->where('approval_status', 'Approved')
            ->where('status', 'Requested')
            ->select('hall_name', 'date', 'start_time', 'end_time', 'course_code')
            ->get();
    
        return response()->json($reservations);
    }


    public function getAvailableHalls(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start'
        ]);

        $allHalls = DB::table('department_mathematics_lecture_halls')->get();
        $availableHalls = [];

        foreach ($allHalls as $hall) {
            $conflictingReservations = DB::table('reservations')
                ->where('hall_id', $hall->hall_id)
                ->where('date', $request->date)
                ->where('approval_status', 'Approved')
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_time', [$request->start, $request->end])
                        ->orWhereBetween('end_time', [$request->start, $request->end])
                        ->orWhereRaw('? BETWEEN start_time AND end_time', [$request->start])
                        ->orWhereRaw('? BETWEEN start_time AND end_time', [$request->end]);
                })
                ->count();

            if ($conflictingReservations === 0) {
                $availableHalls[] = $hall;
            }
        }

        return response()->json($availableHalls);
    }
}

