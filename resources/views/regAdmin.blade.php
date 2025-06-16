@extends('layout')
@section('title','Admin Registration')
@section('content')

<div class="welcome-container">
    
    <h1 class="welcome-title"> Department Of Mathematics <br> Lecture Hall Reservation System</h1>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="registration-card p-4">
            <!-- Display Validation Errors -->
            @if($errors->any())
                <div class="col-12">
                    @foreach($errors->all() as $error)
                        <div class="alert alert-danger">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Session Messages -->
            @if(session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session()->has('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <h2 class="text-center fw-bold mb-4 text-white">Admin Registration</h2>

            <form action="{{ route('adminRegister.post') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label text-white">Full Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <!-- Hidden Role Field -->
                <input type="hidden" name="role" value="admin">

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>

</div>

@endsection