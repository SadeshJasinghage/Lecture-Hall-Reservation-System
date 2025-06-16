@extends('layout')
@section('title', 'Reservation Management')
@section('content')
@php
    use Carbon\Carbon;
    // For table 1, calculate the last updated timestamp from the halls collection
    $lastUpdated = \Carbon\Carbon::now()->format('M d, Y H:i:s');
    $now = Carbon::now();
@endphp

<div class="admin-reservation-container">
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
    <div class = "hall-info-section">
        <h2 class = "hall-info-title" >Lecture Hall Infomation</h2>
        
        <!-- Table 01: Current Hall Status -->
        <div class="last-update">
            <strong>Last Updated:</strong> {{ $lastUpdated }}
        </div>
        <div style="max-height: 300px; overflow-y: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Hall Name</th>
                        <th>Block</th>
                        <th>Seats</th>
                        <th>Projectors</th>
                        <th>A/C</th>
                        <th>Computers</th>
                        <th>Status</th>
                        <th>Reservations</th> 
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($halls as $hall)
                        <tr>
                            <td>{{ $hall->hall_name }}</td>
                            <td>{{ $hall->block }}</td>
                            <td>{{ $hall->seats }}</td>
                            <td>{{ $hall->projectors ?? 'No Projectors' }}</td>
                            <td>{{ $hall->ac ?? 'Not Specified' }}</td>
                            <td>{{ $hall->num_of_computers }}</td>
                            <td>
                                @php
                                    $status = $hall->total_reservations > 0 ? 'Reserved' : 'Available';
                                    $color = $status === 'Reserved' ? 'red' : 'green';
                                @endphp
                                <span class="badge" style="color: {{ $color }}; background-color: white; font-size: 15px; font-weight: bold; padding: 5px 10px; ">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                @if($hall->total_reservations > 0)
                                    <a href="#" class="calendar-icon" data-hall-id="{{ $hall->hall_id }}">
                                        <i class="bi bi-calendar-event"></i>
                                    </a>
                                @else
                                    <span class="text-muted">No Reservations</span>
                                @endif
                            </td>

                            <td>
                                <form action="{{ route('admin.deleteHall', $hall->hall_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this hall?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>  
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
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
    
    <div class = "admin-reservation-confirmation-section">
        <!-- Table 02: Reservation Requests -->
        <h3 class="hall-reservation-confirmation-title">Reservation Requests</h3>

        <!-- Table 02: Reservation management -->
        <div class="last-update">
            <strong>Last Updated:</strong> {{ $lastUpdated }}
        </div>
        <div style="max-height: 300px; overflow-y: auto;">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th>User Name</th> 
                        <th>Role</th>
                        <th>Hall Name</th>
                        <th>Course Code</th>
                        <th>Reservation Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Action</th>
                        
                    </tr>
                </thead>
                <tbody>
                
                    @foreach($activities as $activity)
                    <tr>
                        <td>{{ $activity->user }}</td>
                        <td>{{ $activity->role }}</td>
                        <td>{{ $activity->hall_name }}</td>
                        <td>{{ $activity->course_code }}</td>
                        <td>{{ \Carbon\Carbon::parse($activity->date)->format('M d, Y') }}</td>
                        <td>{{ $activity->start_time }}</td>
                        <td>{{ $activity->end_time }}</td>
                        <td>
                            @if($activity->role !== 'admin') 
                                @if($activity->status == 'Requested' && $activity->approval_status == 'Pending')
                                    <a href="{{ route('admin.approveReservation', $activity->reservation_id) }}" class="btn btn-success">Approve</a>
                                    <a href="{{ route('admin.rejectReservation', $activity->reservation_id) }}" class="btn btn-danger">Reject</a>
                                @elseif($activity->approval_status == 'Approved')
                                    <span class="text-success fw-bold">Approved</span> {{-- Display "Approved" text --}}
                                @endif
                            @endif
                        </td>
                        
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

    <div class="back-to-dashboard-btn-footer">
        <!-- Add Back to Dashboard Button -->
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mb-3  mt-3">
            ← Back to Dashboard
        </a>
    </div>
    

    
    <!-- Floating Add Button -->
    <button class="btn btn-primary rounded-circle" 
            style="position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; font-size: 24px;" 
            data-bs-toggle="modal" data-bs-target="#addHallModal">
        +
    </button>


    <!-- Add New Hall Modal -->
    <div class="modal fade" id="addHallModal" tabindex="-1" aria-labelledby="addHallModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHallModalLabel">Add New Hall</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.addHall') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="hall_name" class="form-label">Hall Name:</label>
                            <input type="text" class="form-control-hall-info" name="hall_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="block" class="form-label">Block Number:</label>
                            <input type="text" class="form-control-hall-info" name="block" required>
                        </div>
                        <div class="mb-3">
                            <label for="seats" class="form-label">Number of Seats:</label>
                            <input type="number" class="form-control-hall-info" name="seats" required>
                        </div>
                        <div class="mb-3">
                            <label for="projectors" class="form-label">Number of Projectors:</label>
                            <input type="number" class="form-control-hall-info" name="projectors" required>
                        </div>
                        <div class="mb-3">
                            <label for="ac" class="form-label">A/C or Non A/C:</label>
                            <select class="form-select" name="ac" required>
                                <option value="A/C">A/C</option>
                                <option value="Non A/C">Non A/C</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="num_of_computers" class="form-label">Number of Computers:</label>
                            <input type="number" class="form-control-hall-info" name="num_of_computers" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


<!-- Modal Structure for  floting window that contain relevent hall reservation details -->


<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog" aria-labelledby="reservationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationModalLabel">Hall Reservations</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4 id="hallName"></h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Reserved Dates</th>
                            <th>Time Period</th>
                            <th>Course Code</th>
                        </tr>
                    </thead>
                    <tbody id="reservationDetails">
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener("DOMContentLoaded", function () {
        // DOM Elements
        const calendarIcons = document.querySelectorAll(".calendar-icon");
        const floatingWindow = document.getElementById("reservationDetailsPopup");
        const closeButton = document.querySelector(".close-btn");
        const popupTitle = document.getElementById("popupTitle");
        const tableBody = document.getElementById("reservationTableBody");
        const filterSelect = document.getElementById("reservationFilter");
        const tableViewBtn = document.getElementById("tableViewBtn");
        const weeklyViewBtn = document.getElementById("weeklyViewBtn");
        const tableView = document.getElementById("tableView");
        const weeklyView = document.getElementById("weeklyView");
        const sortSelect = document.getElementById("reservationSort");

        // Data
        let allReservations = [];
        let currentHallId = null;
        let currentHallName = "";

        // Initialize View Toggle
        tableViewBtn.addEventListener("click", switchToTableView);
        weeklyViewBtn.addEventListener("click", switchToWeeklyView);

        // Calendar Icon Click Handler
        calendarIcons.forEach(icon => {
            icon.addEventListener("click", function (event) {
                event.preventDefault();
                currentHallId = this.getAttribute("data-hall-id");
                currentHallName = this.closest("tr").querySelector("td:first-child").textContent;
                loadReservations();
            });
        });

        // Close Button
        closeButton.addEventListener("click", () => floatingWindow.style.display = "none");
        
        // Click Outside to Close
        window.addEventListener("click", function (event) {
            if (event.target === floatingWindow) {
                floatingWindow.style.display = "none";
            }
        });

        // Filter Change Handler
        filterSelect.addEventListener("change", function () {
            const filterType = this.value;
            renderReservations(filterType);
            
            // Auto-switch to table view if not weekly filter
            if (filterType !== "weekly" && weeklyView.classList.contains("active")) {
                switchToTableView();
            }
        });

        sortSelect.addEventListener("change", function() {
            renderReservations(filterSelect.value);
        });

        // Core Functions
        function loadReservations() {
            showLoadingState();
            
            fetch(`/reservations/${currentHallId}`)
                .then(response => response.json())
                .then(data => {
                    allReservations = data;
                    popupTitle.textContent = currentHallName;
                    renderReservations(filterSelect.value);
                    floatingWindow.style.display = "block";
                })
                .catch(error => {
                    console.error("Error fetching reservations:", error);
                    showErrorState();
                });
        }

        function renderReservations(filterType) {
            const filteredReservations = filterReservations(filterType);
            const sortedReservations = sortReservations(filteredReservations, sortSelect.value);
            
            renderTableView(sortedReservations);
            
            if (weeklyView.classList.contains("active") && filterType === "weekly") {
                renderWeeklyView(sortedReservations);
            }
        }

        function filterReservations(filterType) {
            const now = new Date();
            
            switch(filterType) {
                case "weekly":
                    const currentWeekStart = new Date(now.setDate(now.getDate() - now.getDay()));
                    const currentWeekEnd = new Date(now.setDate(currentWeekStart.getDate() + 6));
                    
                    return allReservations.filter(res => {
                        const resDate = new Date(res.date);
                        return resDate >= currentWeekStart && resDate <= currentWeekEnd;
                    });
                    
                case "monthly":
                    const currentMonth = now.getMonth();
                    const currentYear = now.getFullYear();
                    
                    return allReservations.filter(res => {
                        const resDate = new Date(res.date);
                        return resDate.getMonth() === currentMonth && resDate.getFullYear() === currentYear;
                    });
                    
                default:
                    return allReservations;
            }
        }

        function sortReservations(reservations, sortType) {
            return [...reservations].sort((a, b) => {
                const dateA = new Date(a.date);
                const dateB = new Date(b.date);
                
                switch(sortType) {
                    case 'date-asc':
                        return dateA - dateB;
                    case 'date-desc':
                        return dateB - dateA;
                    case 'time-asc':
                        return a.start_time.localeCompare(b.start_time) || (dateA - dateB);
                    case 'time-desc':
                        return b.start_time.localeCompare(a.start_time) || (dateB - dateA);
                    default:
                        return 0;
                }
            });
        }

        function renderTableView(reservations) {
            tableBody.innerHTML = "";

            if (reservations.length > 0) {
                reservations.forEach(reservation => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${reservation.course_code}</td>
                        <td>${formatDate(reservation.date)}</td>
                        <td>${reservation.start_time} - ${reservation.end_time}</td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center">No Reservations</td>
                    </tr>
                `;
            }
        }

        function renderWeeklyView(reservations) {
            // Clear previous marks
            const cells = document.querySelectorAll(".availability-table td[data-day][data-hour]");
            cells.forEach(cell => {
                cell.innerHTML = "";
                cell.className = ""; // Remove all classes
                cell.style = ""; // Remove all inline styles
                cell.title = "";
            });

            // Mark reserved slots
            reservations.forEach(reservation => {
                const resDate = new Date(reservation.date);
                const dayOfWeek = resDate.getDay(); // 0-6 (Sun-Sat)
                
                if (dayOfWeek >= 1 && dayOfWeek <= 6) { // Mon-Sat
                    const [startHour] = reservation.start_time.split(":").map(Number);
                    const [endHour] = reservation.end_time.split(":").map(Number);
                    
                    for (let hour = startHour; hour <= endHour; hour++) {
                        const cell = document.querySelector(
                            `.availability-table td[data-day="${dayOfWeek}"][data-hour="${hour}"]`
                        );
                        
                        if (cell) {
                            cell.classList.add("reserved");
                            cell.title = `${reservation.course_code}\n${reservation.start_time}-${reservation.end_time}`;
                            
                            if (hour === startHour) {
                                cell.textContent = reservation.course_code;
                                cell.style.fontSize = "0.7em"; // Smaller text for course codes
                            }
                        }
                    }
                }
            });
        }

        function switchToTableView() {
            tableViewBtn.classList.add("active");
            weeklyViewBtn.classList.remove("active");
            tableView.classList.add("active");
            weeklyView.classList.remove("active");
        }

        function switchToWeeklyView() {
            if (filterSelect.value !== "weekly") {
                filterSelect.value = "weekly";
                renderReservations("weekly");
            }
            
            weeklyViewBtn.classList.add("active");
            tableViewBtn.classList.remove("active");
            weeklyView.classList.add("active");
            tableView.classList.remove("active");
        }

        function showLoadingState() {
            popupTitle.textContent = "Loading...";
            tableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center">Loading...</td>
                </tr>
            `;
        }

        function showErrorState() {
            popupTitle.textContent = "Error Loading Data";
            tableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center text-danger">Failed to load data</td>
                </tr>
            `;
        }

        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        }
    });

    // Time Conflict Avoidance Script
    document.addEventListener("DOMContentLoaded", function () {
        const dateInput = document.querySelector('input[name="date"]');
        const hallNameInput = document.getElementById("modal-hall-name");
        const startTimeInput = document.querySelector('input[name="start_time"]');
        const endTimeInput = document.querySelector('input[name="end_time"]');
        const warningMessage = document.getElementById("time-warning-message");

        dateInput?.addEventListener("change", function () {
            const selectedDate = this.value;
            const hallName = hallNameInput.value;

            if (!selectedDate || !hallName) return;

            const hall = document.querySelector(`[data-hall-name="${hallName}"]`);
            if (!hall) return;
            
            const hallId = hall.getAttribute("data-hall-id");
            checkTimeAvailability(hallId, selectedDate);
        });

        function checkTimeAvailability(hallId, date) {
            fetch(`/reservations/${hallId}/${date}`)
                .then(response => response.json())
                .then(reservations => {
                    validateTimeInputs(reservations);
                })
                .catch(error => console.error("Error checking time availability:", error));
        }

        function validateTimeInputs(reservations) {
            if (!startTimeInput || !endTimeInput) return;
            
            const startTime = startTimeInput.value;
            const endTime = endTimeInput.value;
            
            if (!startTime || !endTime) return;
            
            const isConflict = reservations.some(res => {
                return (startTime < res.end_time && endTime > res.start_time);
            });
            
            if (isConflict) {
                warningMessage.innerHTML = `
                    <strong style="color: red;">
                        Selected time conflicts with an existing reservation
                    </strong>
                `;
            } else {
                warningMessage.innerHTML = "";
            }
        }

        // Live validation when times change
        startTimeInput?.addEventListener("change", () => {
            if (dateInput.value && hallNameInput.value) {
                const hall = document.querySelector(`[data-hall-name="${hallNameInput.value}"]`);
                if (hall) {
                    checkTimeAvailability(hall.getAttribute("data-hall-id"), dateInput.value);
                }
            }
        });
        
        endTimeInput?.addEventListener("change", () => {
            if (dateInput.value && hallNameInput.value) {
                const hall = document.querySelector(`[data-hall-name="${hallNameInput.value}"]`);
                if (hall) {
                    checkTimeAvailability(hall.getAttribute("data-hall-id"), dateInput.value);
                }
            }
        });
    });
</script>

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

