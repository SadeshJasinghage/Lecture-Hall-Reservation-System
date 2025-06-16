@extends('layout')
@section('title', 'Lecture Hall Reservation')
@section('content')


<div class="user-reservation-container">

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

    <div class="hall-reservation-section">
        <h2 class="userReservation-title">Find Available Lecture Halls</h2>
        
        <!-- Search Form -->
        <div class="search-card">
            <div class="search-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="reservationDate" class="form-label">Reservation Date</label>
                        <input type="date" class="form-control-Search" id="reservationDate" required>
                    </div>
                    <div class="col-md-3">
                        <label for="startTime" class="form-label">Start Time</label>
                        <input type="time" class="form-control-Search" id="startTime" min="08:00" max="19:00" required>
                    </div>
                    <div class="col-md-3">
                        <label for="endTime" class="form-label">End Time</label>
                        <input type="time" class="form-control-Search" id="endTime" min="08:00" max="19:00" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="searchAvailability">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Availability Guidelines -->
            <div class="availability-info">
                <p class="info-text">
                    <i class="bi bi-info-circle"></i> 
                    Available time slots: 8:00 AM - 7:00 PM<br>
                    Minimum reservation duration: 30 minutes
                </p>
            </div>
        </div>

        <!-- Search Results Floating Window -->
        <div id="availabilityResults" class="availability-results-window">
            <div class="results-header">
                <h3>Available Lecture Halls</h3>
                <button class="close-results">&times;</button>
            </div>
            <div class="results-body">
                <div class="loading-spinner" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="resultsList" class="hall-list"></div>
            </div>
        </div>
    </div>


    <!-- Floating Window -->
    <div id="reservationDetailsPopup" class="floating-window">
        <div class="floating-content">
            <span class="close-btn">&times;</span>
            <h2 id="popupTitle">Hall Reservations</h2>
            
            <!-- Compact filter row -->
            <div class="filter-row" >
                <div class="filter-container">
                    <label for="reservationFilter">
                        <i class="fas fa-filter"></i> Filter:
                    </label>
                    <select id="reservationFilter" class="form-select-sm">
                        <option value="weekly">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="all" selected>All Reservations</option>
                    </select>
                </div>
                <!-- Inside the filter-row div, after the filter controls -->
                <div class="sort-container">
                    <label for="reservationSort">
                        <i class="fas fa-sort"></i> Sort by:
                    </label>
                    <select id="reservationSort" class="form-select-sm">
                        <option value="date-asc">Nearest Date First</option>
                        <option value="date-desc" selected>Farest Date First</option>
                        <option value="time-asc">Time (Early First)</option>
                        <option value="time-desc">Time (Late First)</option>
                    </select>
                </div>
                <div class="view-toggle">
                    <button id="tableViewBtn" class="btn btn-sm btn-outline-primary active">
                        <i class="fas fa-table"></i> Table
                    </button>
                    <button id="weeklyViewBtn" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar-week"></i> Week View
                    </button>
                </div>
            </div>

            <!-- Tab content area with scrolling -->
            <div class="tab-content">
                <!-- Table View -->
                <div id="tableView" class="tab-pane active">
                    <div class="table-container">
                        <table class="compact-table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody id="reservationTableBody">
                                <tr>
                                    <td colspan="3" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Weekly View (hidden by default) -->
                <div id="weeklyView" class="tab-pane">
                    <div class="availability-container">
                        <table class="availability-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($hour = 8; $hour <= 19; $hour++)
                                    <tr>
                                        <td class="time-col">{{ sprintf('%02d:00', $hour) }}</td>
                                        @for ($i = 1; $i <= 6; $i++)
                                            <td data-day="{{ $i }}" data-hour="{{ $hour }}"></td>
                                        @endfor
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <p class="info-message">
                <strong>Note:</strong> Check existing reservations to avoid conflicts.
            </p>
        </div>
    </div>  


    <div class = "user-activity-History-section">
        <!-- Table 02: shows Activity History -->

        <h2 class="user-activity-history-title">Activity History</h2>
        <div style="max-height: 300px; overflow-y: auto;">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th>Requested Date</th> 
                        <th>Hall Name</th>
                        <th>Course Code</th>
                        <th>Reservation Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>Approval Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($activity->requested_date)->format('M d, Y H:i') }}</td>
                                <td>{{ $activity->hall_name }}</td>
                                <td>{{ $activity->course_code }}</td>
                                <td>{{ \Carbon\Carbon::parse($activity->date)->format('M d, Y') }}</td>
                                <td>{{ $activity->start_time }}</td>
                                <td>{{ $activity->end_time }}</td>
                                <td>{{ $activity->status }}</td>
                                <td>
                                    @if($activity->status == 'Requested' && $activity->approval_status != 'Rejected')
                                        @if($activity->approval_status == 'Approved')
                                            <a href="{{ route('user.cancelReservation', $activity->reservation_id) }}" class="btn btn-danger btn-sm text-white">
                                                Cancel Reservation
                                            </a>
                                        @else
                                            <a href="{{ route('user.cancelReservation', $activity->reservation_id) }}" class="btn btn-warning btn-sm">
                                                Cancel Reservation
                                            </a>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $activity->approval_status }}</td>
                            </tr>
                        
                    @endforeach
                </tbody>
            </table>

        </div>


    </div>


    <div class="back-to-dashboard-btn-footer">
        <!-- Add Back to Dashboard Button -->
        <a href="{{ route('user.dashboard') }}" class="btn btn-secondary mb-3 mt-3">
            ← Back to Dashboard
        </a>
    </div>


    <!-- Reservation Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('user.ActivityreserveHall') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="reservationModalLabel">Reserve Lecture Hall</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Instruction Message -->
                        <p class="mb-3 text-muted">Please enter your course/subject code below:</p>
                        
                        <!-- Course Code Input -->
                        <div class="mb-4">
                            <label for="course_code" class="form-label">Course Code</label>
                            <input type="text" name="course_code" class="form-control-booking" required 
                                placeholder="Example: CS401">
                        </div>

                        <!-- Reservation Details Dropdown -->
                        <div class="reservation-details mb-4">
                            <button type="button" class="details-toggle" onclick="toggleDetails()">
                                <i class="fas fa-chevron-down"></i>
                                See your reservation details here
                            </button>
                            <div class="details-content" style="display: none;">
                                <div class="detail-item">
                                    <span class="detail-label">Hall Name:</span>
                                    <span id="detail-hall-name" class="detail-value"></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Reservation Date:</span>
                                    <span id="detail-date" class="detail-value"></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Time Period:</span>
                                    <span id="detail-time" class="detail-value"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Inputs for Reservation Data -->
                        <input type="hidden" name="hall_name" id="modal-hall-name">
                        <input type="hidden" name="date" id="modal-date">
                        <input type="hidden" name="start_time" id="modal-start-time">
                        <input type="hidden" name="end_time" id="modal-end-time">
                        <input type="hidden" name="user_name" value="{{ auth()->user()->name }}">
                        <input type="hidden" name="role" value="{{ auth()->user()->role }}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Confirm Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection



<script>
document.addEventListener("DOMContentLoaded", function() {
    // Auto-fill Reservation Details and Handle Modal
    function handleReserveButton() {
        document.querySelectorAll('.reserve-now-btn').forEach(button => {
            button.addEventListener('click', function() {
                const hallName = this.dataset.hallName;
                const date = this.dataset.date;
                const startTime = this.dataset.start;
                const endTime = this.dataset.end;

                // Set hidden inputs
                document.getElementById('modal-hall-name').value = hallName;
                document.getElementById('modal-date').value = date;
                document.getElementById('modal-start-time').value = startTime;
                document.getElementById('modal-end-time').value = endTime;

                // Set visible details
                document.getElementById('detail-hall-name').textContent = hallName;
                document.getElementById('detail-date').textContent = new Date(date).toLocaleDateString();
                document.getElementById('detail-time').textContent = `${startTime} - ${endTime}`;

                // Show modal
                new bootstrap.Modal(document.getElementById('reservationModal')).show();
            });
        });
    }

    // Details Toggle Function
    function toggleDetails() {
        const content = document.querySelector('.details-content');
        const arrow = document.querySelector('.details-toggle i');
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
        arrow.style.transform = content.style.display === 'block' ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    // Fading Alerts
    setTimeout(function() {
        document.querySelectorAll('#floating-alert-container .alert').forEach(alert => {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 1000);
        });
    }, 3000);

    // Search Availability Function (Updated)
    const searchBtn = document.getElementById('searchAvailability');
    if(searchBtn) {
        searchBtn.addEventListener('click', function() {
            const date = document.getElementById('reservationDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;

            if (!date || !startTime || !endTime) {
                alert('Please fill all required fields');
                return;
            }

            const loadingSpinner = document.querySelector('.loading-spinner');
            const resultsWindow = document.getElementById('availabilityResults');
            const hallList = document.getElementById('resultsList');

            loadingSpinner.style.display = 'flex';
            resultsWindow.style.display = 'block';

            fetch(`/api/available-halls?date=${date}&start=${startTime}&end=${endTime}`)
                .then(response => response.json())
                .then(data => {
                    hallList.innerHTML = data.length > 0 ? '' : 
                        `<div class="text-center text-muted">No available halls found</div>`;

                    data.forEach(hall => {
                        const hallCard = document.createElement('div');
                        hallCard.className = 'hall-card';
                        hallCard.innerHTML = `
                            <div class="hall-info">
                                <h5>${hall.hall_name}</h5>
                                <div class="hall-features">
                                    <span>Seats: ${hall.seats}</span>
                                    <span>Block: ${hall.block}</span>
                                    <span>Projectors: ${hall.projectors}</span>
                                    <span>A/C: ${hall.ac}</span>
                                </div>
                            </div>
                            <button class="reserve-now-btn" 
                                data-hall-name="${hall.hall_name}"
                                data-date="${date}"
                                data-start="${startTime}"
                                data-end="${endTime}">
                                Reserve Now
                            </button>
                        `;
                        hallList.appendChild(hallCard);
                    });

                    handleReserveButton(); // Rebind reserve buttons after new results
                    loadingSpinner.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingSpinner.style.display = 'none';
                    hallList.innerHTML = `<div class="text-center text-danger">Error loading data</div>`;
                });
        });
    }

    // Close Results Window
    document.querySelector('.close-results')?.addEventListener('click', () => {
        document.getElementById('availabilityResults').style.display = 'none';
    });

    // Initialize Details Toggle
    document.querySelector('.details-toggle')?.addEventListener('click', toggleDetails);
});
</script>





