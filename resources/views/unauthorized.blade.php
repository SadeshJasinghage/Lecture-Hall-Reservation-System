@extends('layout')
@section('title', 'Login')
@section('content')
<div class="container text-center">
    <h2>Access Denied</h2>
    <p>You do not have permission to access this page.</p>
    <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
</div>
@endsection
