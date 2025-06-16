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
    </div>
    <div class="welcome-box2">
        <h4>Welcome to Department Of Mathematics, University Of Colombo</h4>
        <p>Hi, {{ auth()->user()->name }}!</p>
    </div>

    <!-- Transparent Card View for Make Reservations -->
    <div class="reservation-card"  onclick="window.location.href='{{ route('guest.reservation') }}'">
        <div class="card-body">
            <h4 class="card-title">Reserve Your Lecture Hall Here..</h4>
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
