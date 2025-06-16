<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApprovalNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GuestController extends Controller
{
    public function index()
    {
        return view('guest.guestDashboard'); 
    }

    // Show Reservations Table (To fetch data here we left join user_guest_activity_history with department_mathematics_lecture_halls based on hall id  )

    public function showReservation()
    {
        $halls = DB::table('department_mathematics_lecture_halls')
                ->leftJoin('reservations', function ($join) {
                    $join->on('department_mathematics_lecture_halls.hall_id', '=', 'reservations.hall_id')
                        ->where('reservations.approval_status', '=', 'Approved');
                })
                ->select(
                    'department_mathematics_lecture_halls.*',
                    DB::raw('COUNT(reservations.reservation_id) as total_reservations') // Use the correct column name
                )
                ->groupBy('department_mathematics_lecture_halls.hall_id')
                ->get();
        $requests = DB::table('reservations')
                ->get();

        // Fetch only the logged-in user's activity history, filtering out guest records
        $activities = DB::table('user_guest_activity_history')
                ->where('user', auth()->user()->name) // Ensure it belongs to the logged-in user
                ->where('role', 'Guest') // Exclude guest reservations
                ->orderBy('requested_date', 'desc')
                ->get();


        return view('guest.guestReservation', compact('halls','requests','activities'));
    }


    public function storeActivity_and_Reservation(Request $request)
    {
        $validated = $request->validate([
            'hall_name' => 'required|string',
            'user_name' => 'required|string',
            'role' => 'required|string',
            'course_code' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i', // Validate time format
            'end_time' => 'required|date_format:H:i|after:start_time', // Ensure end time is after start time
        ]);
    
        // Fetch hall_id based on hall_name
        $hall = DB::table('department_mathematics_lecture_halls')
                ->where('hall_name', $validated['hall_name'])
                ->first();
    
        if (!$hall) {
            return redirect()->back()->with('error', 'Selected hall does not exist.');
        }



    
        // Insert into reservations table and get the inserted ID
        $reservationId = DB::table('reservations')->insertGetId([
            'user_id'    => auth()->id(),
            'hall_id'    => $hall->hall_id,
            'hall_name'       => $validated['hall_name'],
            'user_name'   => $validated['user_name'],
            'role'   => $validated['role'],
            'course_code'       => $validated['course_code'],
            'date'       => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time'   => $validated['end_time'],
            'created_at' => now(),
            'updated_at' => now(),
            'approval_status'=> 'Pending',
            'status'         => 'Requested',
        ]);

        // Store user activity in the user_guest_activity_history table
        DB::table('user_guest_activity_history')->insert([
            'user'           => auth()->user()->name, // Store username
            'role'           => auth()->user()->role,
            'hall_id'        => $hall->hall_id,
            'requested_date' => now(), 
            'reservation_id' => $reservationId, 
            'hall_name'      => $validated['hall_name'],
            'course_code'    => $validated['course_code'],
            'date'           => $validated['date'],
            'start_time'     => $validated['start_time'],
            'end_time'       => $validated['end_time'],
            'status'         => 'Requested',
            'approval_status'=> 'Pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    


    
        return redirect()->back()->with('success', 'Reservation successfully submitted.');
    }
    

    public function cancelReservation($reservationId)
    {
        // Fetch the reservation details
        $requests = DB::table('reservations')->where('reservation_id', $reservationId)->first();
        
        if (!$requests) {
            return redirect()->back()->with('error', 'Reservation not found.');
        }
    
        // Update the reservation status to 'Cancelled' in user_guest_activity_history
        DB::table('user_guest_activity_history')
            ->where('reservation_id', $reservationId)
            ->update([
                'status' => 'Cancelled',
                'updated_at' => Carbon::now(),
            ]);

        DB::table('reservations')
            ->where('reservation_id', $reservationId)
            ->update([
                'status' => 'Cancelled',
                'updated_at' => Carbon::now(),
            ]); 
            
        // Check if there are other active reservations for this hall
        $activeReservations = DB::table('reservations')
            ->where('hall_id', $requests->hall_id)
            ->where('approval_status', 'Approved')
            ->where('status', 'Requested')
            ->exists(); // Check if any such reservation exists

        // Update the corresponding hall status to "Available" in department_mathematics_lecture_halls
        if (!$activeReservations) {
            DB::table('department_mathematics_lecture_halls')
                ->where('hall_id', $requests->hall_id)
                ->update([
                    'status' => 'Available',
                    'date' => null,
                ]);
        }
    
        // Remove reservation from the reservations table
        DB::table('reservations')->where('reservation_id', $reservationId)->delete();
    
        return redirect()->back()->with('success', 'Reservation cancelled successfully.');
    }







}
