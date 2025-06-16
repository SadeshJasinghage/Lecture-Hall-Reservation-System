@extends('layout')
@section('title', 'Admin Dashboard')
@section('content')
<div class="dashboard-container">
    <!-- Floating Alert Container -->
    <div id="floating-alert-container" style="position: fixed; top: 100px; right: 20px; z-index: 1000; max-width: 300px;">
         @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $error }}
                </div>
            @endforeach
         @endif

         @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
            </div>
         @endif

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
            </div>
        @endif
    </div >
    <!-- Left Corner Welcome Message -->
    <div class="welcome-box2">
        <h4>Welcome to Department Of Mathematics, University Of Colombo</h4>
        <p>Hi, {{ auth()->user()->name }}!</p>
    </div>

    <div class="admin-card-holder">
        <!-- Transparent Card View for user Management -->
        <div class="admin-userapproval-card"  onclick="window.location.href='{{ route('admin.userManagement') }}'">
            <div class="card-body">
                <h4 class="card-title">Manage User and Guest Approvals</h4>
            </div>   
        </div>
        

        <!-- Transparent Card View for Reservation Management -->
        <div class="admin-reservation-card" onclick="window.location.href='{{ route('admin.reservationManagement') }}'">
            <div class="card-body">
                <h4 class="card-title">Manage Lecture Hall Reservations</h4>
            </div>
        </div>

    </div>
</div>
@endsection

<script> // Js for floting and fading error massage window
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            let alertElements = document.querySelectorAll('#floating-alert-container .alert');
            alertElements.forEach(function(alert) {
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 1000); // Remove after fade out
            });
        }, 3000); // 3 seconds before fade out starts
    });
</script>
