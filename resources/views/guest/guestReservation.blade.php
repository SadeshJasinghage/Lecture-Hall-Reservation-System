@extends('layout')
@section('title', 'Lecture Hall Reservation')
@section('content')
<div class="user-reservation-container">
    
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

    <div class = "hall-rerservation-section">
        <h2 class="userReservation-title">Reserve Your Hall Here....</h2>

        <!-- Table 01: shows all lecuture halls and status of them -->
        <div style="max-height: 600px; overflow-y: auto;">
            <table class="table mt-4">
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
                        <th>Action</th>
                        
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
                            
                            <button type="button" class="btn btn-primary reserve-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#reservationModal"
                                data-hall-id="{{ $hall->hall_id  }}"
                                data-hall-name="{{ $hall->hall_name }}">
                                Reserve
                            </button>
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
                                            <a href="{{ route('guest.cancelReservation', $activity->reservation_id) }}" class="btn btn-danger btn-sm text-white">
                                                Cancel Reservation
                                            </a>
                                        @else
                                            <a href="{{ route('guest.cancelReservation', $activity->reservation_id) }}" class="btn btn-warning btn-sm">
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
        <a href="{{ route('guest.dashboard') }}" class="btn btn-secondary mb-3 mt-3">
            ← Back to Dashboard
        </a>
    </div>


    <!-- Add Back to Dashboard Button -->


    <!-- Reservation Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('guest.ActivityreserveHall') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="reservationModalLabel">Reserve Lecture Hall</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                        <!-- Hall Name (Auto-filled) -->
                        <div class="mb-3"> 
                            <label for="hall_name" class="form-label">Hall Name</label>
                            <input type="text" name="hall_name" id="modal-hall-name" class="form-control-booking" readonly>
                        </div>

                        <!-- Course Code -->
                        <div class="mb-3">
                            <label for="course_code" class="form-label">Course Code</label>
                            <input type="text" name="course_code" class="form-control-booking" required>
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" name="date" class="form-control-booking" required>
                        </div>

                        <!-- Lecture Start Time -->
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Lecture Start Time</label>
                            <input type="time" name="start_time" class="form-control-booking" required>
                        </div>

                        <!-- Lecture End Time -->
                        <div class="mb-3">
                            <label for="end_time" class="form-label">Lecture End Time</label>
                            <input type="time" name="end_time" class="form-control-booking" required>
                        </div>

                        <!-- User Name (Auto-fill) -->
                        <div class="mb-3">
                            <label for="user_name" class="form-label">User Name</label>
                            <input type="text" name="user_name" class="form-control-booking" value="{{ auth()->user()->name }}" readonly>
                        </div>

                        <!-- Role (Auto-fill) -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" name="role" class="form-control-booking" value="{{ auth()->user()->role }}" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

<script> // Js for Autofill Hall name when click on "Reserve" Button
    document.addEventListener("DOMContentLoaded", function () {
        const reserveButtons = document.querySelectorAll(".reserve-btn");
        const hallNameInput = document.getElementById("modal-hall-name");

        reserveButtons.forEach(button => {
            button.addEventListener("click", function () {
                let hallName = this.getAttribute("data-hall-name");
                hallNameInput.value = hallName; // Set the hall name in the input field
            });
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


<style>
    /* Floating Window */
    .floating-window {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 700px;
        background:rgba(224, 250, 224, 0.9); /* Light green with transparency */
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        text-align: center;
    }

    /* Table Styling */
    .styled-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: rgba(255, 255, 255, 0.8); /* Slight transparency */
        border-radius: 8px;
    }

    .styled-table th {
        background: rgba(50, 150, 50, 0.85); /* Darker green */
        color: white;
        padding: 10px;
        text-align: center;
    }

    .styled-table td {
        background: rgba(180, 255, 180, 0.7); /* Lighter green */
        padding: 10px;
        border: 1px solid #4CAF50;
        text-align: center;
    }

    .info-message {
        margin-top: 15px;
        font-size: 14px;
        color: #333;
        font-weight: bold;
        background: rgba(255, 255, 255, 0.9); /* Light yellow background */
        padding: 10px;
        border-radius: 8px;
        border-left: 5px solid #FFA500; /* Orange border for emphasis */
        text-align: center;
    }

    /* Close Button */
    .close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #ff4d4d;
    }

    .close-btn:hover {
        color: #cc0000;
    }
</style>





