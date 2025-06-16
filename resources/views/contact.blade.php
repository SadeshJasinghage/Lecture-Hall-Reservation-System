@extends('layout')
@section('title','Contact us')
@section('content')
    <div class="container">
        <form class="ms-auto me-auto mt-5 p-4 shadow rounded bg-light" style="max-width: 500px;">
           
            <h2 class="text-center text-primary">Contact Us</h2>
            <p class="text-center text-muted">We'd love to hear from you! Fill out the form below.</p>

            <!-- Full Name -->
            <div class="mb-3">
                <label class="form-label fw-bold">Full Name</label>
                <input type="text" class="form-control" name="name" placeholder="Enter your name" required>
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <label class="form-label fw-bold">Email Address</label>
                <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
            </div>

            <!-- Subject -->
            <div class="mb-3">
                <label class="form-label fw-bold">Subject</label>
                <input type="text" class="form-control" name="subject" placeholder="Enter the subject" required>
            </div>

            <!-- Message -->
            <div class="mb-3">
                <label class="form-label fw-bold">Message</label>
                <textarea class="form-control" name="message" rows="4" placeholder="Write your message here" required></textarea>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary w-100">Send Message</button>
            </div>
        </form>

    </div>
@endsection