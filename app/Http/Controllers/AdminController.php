<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApprovalNotification;
use Illuminate\Support\Facades\DB;
use App\Mail\ReservationApprovalMail;
use App\Mail\ReservationRejectedMail;
use Carbon\Carbon;



class AdminController extends Controller
{    
    //Show admin dash board............................
    public function showDashboard()
    {
        return view('admin.adminDashboard');
    }

    //Show userManagement dash board............................

    public function index()
    {
        // Fetch all users from the database
        $users = User::all();

        // Pass the users variable to the view
        return view('admin.userManagement', compact('users'));
    }

    //Functioning userManagement dash board............................

    public function approveUser($id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return redirect()->back()->with("error", "User not found");
        }
    
        $user->status = "approved";
        $user->save();
    
        // Send an approval email
        Mail::to($user->email)->send(new ApprovalNotification($user));
    
        return redirect()->back()->with("success", "User approved successfully!");
    }

    public function removeUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }
        return redirect()->route('admin.userManagement')->with('success', 'User removed successfully.');
    }


    //Show reservationManagement dash board............................
    
    public function showReservationManagement()
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
    

        
        $requests = DB::table('reservations')->get();

        $activities = DB::table('user_guest_activity_history')
                ->where('approval_status', '!=', 'Rejected')  // Exclude rejected reservations
                ->where(function ($query) {
                    $query->where('status', '!=', 'Cancelled') // Exclude canceled reservations
                        ->orWhere('approval_status', 'Approved'); // Keep only approved reservations even if canceled later
                })
                ->where('requested_date', '>=', now()->subDays(7))
                ->orderBy('updated_at', 'desc') // Sort by latest updated request first
                ->get();
                

        $users = User::all();
        return view('admin.reservationManagement', compact('halls','requests','activities',));
    }

    //Functioning reservationManagement dash board............................

    ////Function No:1 Adding new Hall to Data Base and Delete Hall

    public function addHall(Request $request)
    {
        $validatedData = $request->validate([
            'hall_name' => 'required|string|unique:department_mathematics_lecture_halls,hall_name',
            'block' => 'required|string',
            'seats' => 'required|integer', // Ensure seats is required
            'projectors' => 'nullable|integer',
            'ac' => 'nullable|string',
            'num_of_computers' => 'nullable|integer',
            
        ]);

        DB::table('department_mathematics_lecture_halls')->insert([
            'hall_name' => $request->hall_name,
            'block' => $request->block,
            'seats' => $request->seats,
            'projectors' => $request->projectors,
            'ac' => $request->ac,
            'num_of_computers' => $request->num_of_computers,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.reservationManagement')->with('success', 'New hall added successfully!');
    }

    public function deleteHall($id)
    {
        // Find the hall by ID
        $hall = DB::table('department_mathematics_lecture_halls')->where('hall_id', $id)->first();
    
        if (!$hall) {
            return redirect()->back()->with('error', 'Hall not found.');
        }
    
        // Delete the hall
        DB::table('department_mathematics_lecture_halls')->where('hall_id', $id)->delete();
    
        return redirect()->back()->with('success', 'Hall deleted successfully.');
    }
    


    ////Function No:2 manage user Reservation Approvals

    public function approveReservation($reservationId)
    {
        $requests = DB::table('reservations')->where('reservation_id', $reservationId)->first();
        if (!$requests) {
            return redirect()->back()->with('error', 'Reservation not found.');
        }
    
        $user = User::find($requests->user_id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
    
        
        DB::table('reservations')
            ->where('reservation_id', $reservationId)
            ->update([
                'approval_status' => 'Approved',
                'updated_at' => Carbon::now(),
            ]);
    
        
        DB::table('user_guest_activity_history')
            ->where('reservation_id', $reservationId)
            ->update([
                'approval_status' => 'Approved',
                'updated_at' => Carbon::now(),
            ]);
    
        
        $hasOtherReservations = DB::table('reservations')
            ->where('hall_id', $requests->hall_id)
            ->where('approval_status', 'Approved')
            ->exists();
    
        
        DB::table('department_mathematics_lecture_halls')
            ->where('hall_id', $requests->hall_id)
            ->update([
                'status' => $hasOtherReservations ? 'Reserved' : 'Available',
            ]);
    
        
        Mail::to($user->email)->send(new ReservationApprovalMail($user));
    
        return redirect()->back()->with('success', 'Reservation approved successfully.');
    }
    

    public function rejectReservation($reservationId)
    {
        $requests = DB::table('reservations')->where('reservation_id', $reservationId)->first();
        if (!$requests) {
            return redirect()->back()->with('error', 'Reservation not found.');
        }
    
        $user = User::find($requests->user_id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
    
        
        $hasOtherReservations = DB::table('reservations')
            ->where('hall_id', $requests->hall_id)
            ->where('approval_status', 'Approved')
            ->where('reservation_id', '!=', $reservationId) // Exclude the current rejected one
            ->exists();
    
        
        DB::table('reservations')->where('reservation_id', $reservationId)->delete();
    
        
        DB::table('department_mathematics_lecture_halls')
            ->where('hall_id', $requests->hall_id)
            ->update([
                'status' => $hasOtherReservations ? 'Reserved' : 'Available',
            ]);
    
        
        DB::table('user_guest_activity_history')
            ->where('reservation_id', $reservationId)
            ->update([
                'approval_status' => 'Rejected',
                'updated_at' => Carbon::now(),
            ]);
    
        
        Mail::to($user->email)->send(new ReservationRejectedMail($user));
    
        return redirect()->back()->with('success', 'Reservation rejected successfully.');
    }
    



}
