<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\UserMiddleware;
use App\Http\Controllers\UserController;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ReservationController;
use App\Models\User;


Route::get('/', function () {
    if (Auth::check() && Auth::user()->status !== 'approved') {
        return redirect(route('login'))->with("error", "Your registration request is not approved yet.");
    }
    return view('welcome');
})->name('Home');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

//Login Registration view and post routes

Route::get('/login',[AuthManager::class, 'login'])->name('login');
Route::post('/login',[AuthManager::class, 'loginPost'])->name('login.post');

Route::get('/register',[AuthManager::class, 'register'])->name('userRegister');
Route::get('/regGuest',[AuthManager::class, 'guestRegister'])->name('guestRegister');
Route::get('/regAdmin',[AuthManager::class, 'adminRegister'])->name('adminRegister');

Route::post('/register',[AuthManager::class, 'registerPost'])->name('userRegister.post');
Route::post('/regGuest',[AuthManager::class, 'guestRegisterPost'])->name('guestRegister.post');
Route::post('/regAdmin',[AuthManager::class, 'adminRegisterPost'])->name('adminRegister.post');

//logout route

Route::get('/logout',[AuthManager::class, 'logout'])->name('logout');

// Route to show hall reservation infomation on floating window
Route::get('/reservations/{hallId}', [ReservationController::class, 'getReservations']);



// rote made for avoiding time overlaping for perticular hall over the perticular date
Route::get('/reservations/{hallId}/{date}', [ReservationController::class, 'getReservationsByDate']);




// Applying Middleware in Routes 

// Protect admin routes

Route::middleware(AdminMiddleware::class)->group(function () {
    Route::get('/admin/adminDashboard', [AdminController::class, 'showDashboard'])->name('admin.dashboard');
    Route::get('/admin/userManagement', [AdminController::class, 'index'])->name('admin.userManagement');
    Route::get('/admin/approveUser/{id}', [AdminController::class, 'approveUser'])->name('approveUser');
    Route::get('/admin/removeUser/{id}', [AdminController::class, 'removeUser'])->name('removeUser');
    Route::get('/admin/reservation-management', [AdminController::class, 'showReservationManagement'])->name('admin.reservationManagement');
    Route::post('/admin/add-hall', [AdminController::class, 'addHall'])->name('admin.addHall');
    Route::get('/admin/approveReservation/{id}', [AdminController::class, 'approveReservation'])->name('admin.approveReservation');
    Route::get('/admin/rejectReservation/{id}', [AdminController::class, 'rejectReservation'])->name('admin.rejectReservation');
    Route::delete('/admin/delete-hall/{id}', [AdminController::class, 'deleteHall'])->name('admin.deleteHall');

});

// insert user routes

Route::middleware(UserMiddleware::class)->group(function () {
    Route::get('/user/userDashboard', [UserController::class, 'showUserDashboard'])->name('user.dashboard');
    Route::get('/user/userReservation', [UserController::class, 'showReservation'])->name('user.reservation');
    Route::post('/user/userReservation', [UserController::class, 'storeActivity_and_Reservation'])->name('user.ActivityreserveHall');
    Route::get('/user/cancelReservation/{id}', [UserController::class, 'cancelReservation'])->name('user.cancelReservation');    
    // API routes
    Route::get('/api/available-halls', [ReservationController::class, 'getAvailableHalls']);
    Route::get('/api/reservations/{date}', [UserController::class, 'getDateReservations'])->middleware('auth');
    Route::get('/api/hall-reservations/{hallId}', [UserController::class, 'getHallReservations'])->middleware('auth');

});


// insert guest routes

Route::middleware(GuestMiddleware::class)->group(function () {
    Route::get('/guest/guestDashboard', [GuestController::class, 'index'])->name('guest.dashboard');
    Route::get('/guest/userReservation', [GuestController::class, 'showReservation'])->name('guest.reservation');
    Route::post('/guest/userReservation', [GuestController::class, 'storeActivity_and_Reservation'])->name('guest.ActivityreserveHall');
    Route::get('/guest/cancelReservation/{id}', [GuestController::class, 'cancelReservation'])->name('guest.cancelReservation');
});

// view unauthorized message for un authorized access

Route::get('/unauthorized', function () {
    return view('unauthorized');
})->name('unauthorized');


