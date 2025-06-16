@extends('layout')
@section('title', 'User Management')
@section('content')
<div class="container mt-4 ms-auto me-auto mt-3 mb-3">

        <!-- Floating Alert Container -->
        <div id="floating-alert-container" style="position: fixed; top: 80px; right: 20px; z-index: 1000; max-width: 300px;">
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

    <h2>All Users & Guest Management</h2>

    <div class="col-md-6"  style="width: 1000px">
                <div class="card shadow border-0">
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>{{ ucfirst($user->status) }}</td>
                            <td>
                                @if($user->role !== 'admin')  {{-- Only show for non-admins --}}
                                    @if($user->status == 'pending')
                                        <a href="{{ route('approveUser', $user->id) }}" class="btn btn-success">Approve</a>
                                    @endif
                                    <a href="{{ route('removeUser', $user->id) }}" class="btn btn-danger">Remove</a>
                                @else
                                    <span class="text-muted">Admin</span>  {{-- Show "Admin" text instead --}}
                                @endif

                            </td>
                        </tr>
                        @endforeach
                    </table>

                </div>
    </div>

    <!-- Add Back to Dashboard Button -->
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mb-3 ms-auto me-auto mt-3">
        ← Back to Dashboard
    </a>


</div>
@endsection

<script>
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
