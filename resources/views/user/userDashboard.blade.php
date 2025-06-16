@extends('layout')
@section('title', 'User Dashboard')
@section('content')

@php
    use Carbon\Carbon;
@endphp

<div class="dashboard-container container py-4">
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

    <!-- Welcome Message -->
    <div class="welcome-box2">
        <h4>Welcome to Department Of Mathematics, University Of Colombo</h4>
        <p>Hi, {{ auth()->user()->name }}!</p>
    </div>

    <!-- Grid Layout for Widgets -->

    <div class="d-flex overflow-auto py-3" style="max-width: 1400px;">
        
        <!-- Upcoming Reservations Summary -->
        <div class="card_shadow_rounded flex-shrink-0 mx-2" style="width: 300px; height: 300px;">
            <div class="card-header text-white" style="background-color: #2c3e50;">
                <h5 class="mb-0">Monthly Reservations</h5>
            </div>
            <div class="card-body overflow-auto p-2">
                @php
                    $reservedDates = $upcomingReservations->pluck('date')->map(function ($date) {
                        return \Carbon\Carbon::parse($date)->format('Y-m-d');
                    })->toArray();
                    
                    $startDate = now()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
                    $endDate = now()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
                @endphp

                <div class="calendar-container">
                    <div class="calendar-header text-center mb-2 fw-bold">
                        {{ now()->format('F Y') }}
                    </div>
                    
                    <div class="calendar-grid">
                        <!-- Weekdays Header -->
                        <div class="calendar-row">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <div class="calendar-weekday">{{ $day }}</div>
                            @endforeach
                        </div>

                        <!-- Calendar Days -->
                        @while($startDate <= $endDate)
                            <div class="calendar-row">
                                @for($i = 0; $i < 7; $i++)
                                    @php
                                        $isCurrentMonth = $startDate->month == now()->month;
                                        $isReserved = in_array($startDate->format('Y-m-d'), $reservedDates);
                                    @endphp
                                    
                                    <div class="calendar-day 
                                        {{ !$isCurrentMonth ? 'other-month' : '' }} 
                                        {{ $isReserved ? 'reserved-day' : '' }}"
                                        data-date="{{ $startDate->format('Y-m-d') }}"
                                        data-reserved="{{ $isReserved ? 'true' : 'false' }}">
                                        {{ $startDate->day }}
                                    </div>
                                    
                                    @php $startDate->addDay(); @endphp
                                @endfor
                            </div>
                        @endwhile
                    </div>
                </div>
            </div>
        </div>

        <!-- Hall Availability -->
        <div class="card_shadow_rounded flex-shrink-0 mx-2" style="width: 300px; height: 300px;">
            <div class="card-header text-white" style="background-color: #2c3e50;">
                <h5 class="mb-0">Check Hall Availability</h5>
            </div>
            <div class="card-body p-2" style="height: 250px; overflow-y: auto; -ms-overflow-style: none; scrollbar-width: none;">
                <div class="hall-grid">
                    @foreach ($halls as $hall)
                        <div class="hall-box" 
                            style="background-color: {{ in_array($hall->hall_id, $reservedHalls) ? '#dc3545' : '#6adc35' }};"
                            onclick="showHallCalendar('{{ $hall->hall_id }}', '{{ $hall->hall_name }}')">
                            <span class="hall-name">{{ $hall->hall_name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        
        <!-- Reservation History Mini View -->
        <div class="card_shadow_rounded flex-shrink-0 mx-2" style="width: 300px; height: 300px;">
            <div class="card-header text-white" style="background-color: #2c3e50;">
                <h5 class="mb-0">Reservation History</h5>
            </div>
            <div class="card-body p-0 d-flex flex-column">
                <!-- Status Selector -->
                <div class="d-flex justify-content-between border-bottom">
                    <button class="btn btn-history-type active flex-fill" onclick="showHistory('approved')">
                        Approved
                    </button>
                    <button class="btn btn-history-type flex-fill" onclick="showHistory('rejected')">
                        Rejected
                    </button>
                    <button class="btn btn-history-type flex-fill" onclick="showHistory('cancelled')">
                        Cancelled
                    </button>
                </div>

                <!-- History Panels -->
                <div class="flex-grow-1" style="height: 200px; overflow-y: auto; -ms-overflow-style: none; scrollbar-width: none;">


                    <!-- Approved Reservations -->
                    <div id="approvedHistory" class="history-panel d-none">  <!-- Add d-none here -->
                        @if($approvedHistory->isEmpty())
                            <p class="text-muted p-2">No approved reservations</p>
                        @else
                            <ul class="list-group text-start">
                                @foreach($approvedHistory as $history)
                                    <li class="list-group-item small">
                                        <strong>{{ $history->hall_name }}</strong><br>
                                        {{ \Carbon\Carbon::parse($history->date)->format('M d, Y') }}<br>
                                        {{ $history->start_time }} - {{ $history->end_time }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <!-- Rejected Reservations -->
                    <div id="rejectedHistory" class="history-panel d-none">
                        @if($rejectedHistory->isEmpty())
                            <p class="text-muted p-2">No rejected reservations</p>
                        @else
                            <ul class="list-group text-start">
                                @foreach($rejectedHistory as $history)
                                    <li class="list-group-item small">
                                        <strong>{{ $history->hall_name }}</strong><br>
                                        {{ \Carbon\Carbon::parse($history->date)->format('M d, Y') }}<br>
                                        {{ $history->start_time }} - {{ $history->end_time }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <!-- Cancelled Reservations -->
                    <div id="cancelledHistory" class="history-panel d-none">
                        @if($cancelledHistory->isEmpty())
                            <p class="text-muted p-2">No cancelled reservations</p>
                        @else
                            <ul class="list-group text-start">
                                @foreach($cancelledHistory as $history)
                                    <li class="list-group-item small">
                                        <strong>{{ $history->hall_name }}</strong><br>
                                        {{ \Carbon\Carbon::parse($history->date)->format('M d, Y') }}<br>
                                        {{ $history->start_time }} - {{ $history->end_time }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Panel -->
        <div class="card_shadow_rounded flex-shrink-0 mx-2" style="width: 300px; height: 300px;">
            
            <div class="card-header  text-white " style="background-color: #2c3e50;>
                <h5 class="mb-0">Notifications</h5>
            </div>
            <div class="card-body">
                @if (!empty($notifications) && count($notifications))
                    <ul class="list-group text-start">
                        @foreach ($notifications as $note)
                            <li class="list-group-item">{{ $note }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>No new notifications at the moment.</p>
                @endif
            </div>
            
        </div>


    </div>


    <!-- Transparent Card View for Make Reservations -->
    <div class="reservation-card" onclick="window.location.href='{{ route('user.reservation') }}'">
        <div class="card-body">
            <h4 class="card-title">Reserve Your Lecture Hall Here..</h4>
        </div>
    </div>
</div>
@endsection


<!-- Hall Calendar Panel Modal -->
<div class="modal fade" id="hallCalendarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header with only Hall Name -->
            <div class="modal-header bg-primary text-white p-3">
                <h5 class="modal-title m-0">
                    Hall Availability Panel - <span id="currentHallName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                <div class="d-flex h-100">
                    <!-- Left Column: Calendar + Filters -->
                    <div class="calendar-sidebar">
                        <!-- Calendar Header with Month/Year and Arrows -->
                        <div class="calendar-header bg-light px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="shiftHallMonth(-1)">
                                &laquo;
                            </button>
                            <h6 class="m-0" id="currentMonthYear">Month Year</h6>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="shiftHallMonth(1)">
                                &raquo;
                            </button>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="calendar-container p-3">
                            <div class="calendar-grid" id="hallCalendarGrid"></div>
                        </div>

                        <!-- Filters Section -->
                        <div class="controls-container p-3 border-top">
                            <button class="btn btn-primary w-100 mb-3" id="applyFilters">
                                <i class="bi bi-funnel"></i> Apply Filters
                            </button>
                            <div class="form-check mb-2 ms-3">
                                <input class="form-check-input" type="checkbox" id="filterMorning">
                                <label class="form-check-label" for="filterMorning">Morning Sessions</label>
                            </div>
                            <div class="form-check mb-2 ms-3">
                                <input class="form-check-input" type="checkbox" id="filterAfternoon">
                                <label class="form-check-label" for="filterAfternoon">Afternoon Sessions</label>
                            </div>
                            <div class="form-check ms-3">
                                <input class="form-check-input" type="checkbox" id="filterEquipment">
                                <label class="form-check-label" for="filterEquipment">With Equipment</label>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Dynamic Content Panel -->
                    <div class="col-9 border-start p-3" id="calendarContentPanel">
                        <!-- Top Bar with Dropdown -->
                        <div class="d-flex justify-content-end mb-3 me-3">
                            <select id="calendarViewMode" class="form-select w-auto">
                            <option value="day" selected>Day</option>
                            <option value="week">Week</option>
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Content Container -->
                    <div id="calendarDynamicContent">
                        <!-- Default view is Day -->
                        <table class="timetable-table w-100 border">
                        <tbody id="dynamicTimetableBody">
                            @for ($hour = 8; $hour < 19; $hour++)
                            <tr>
                                <td class="time-label border" style="width: 100px;">
                                {{ \Carbon\Carbon::createFromTime($hour)->format('h:i A') }}
                                </td>
                                <td class="reservation-slot border" data-hour="{{ $hour }}"></td>
                            </tr>
                            @endfor
                        </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>



<!-- ReservationDetail Modal -->
<div class="modal fade" id="reservationDetailModal" tabindex="-1" aria-labelledby="reservationDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="reservationDetailModalLabel">
          Reservation Details for <span id="selectedDate"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="timetable-table w-100">
          <tbody id="timetableBody">
            @for ($hour = 8; $hour < 19; $hour++)
              <tr>
                <td class="time-label">
                  {{ \Carbon\Carbon::createFromTime($hour)->format('h:i A') }}
                </td>
                <td class="reservation-slot" data-hour="{{ $hour }}"></td>
              </tr>
            @endfor
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>




<script>
    // JS for floating and fading error message window
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            let alertElements = document.querySelectorAll('#floating-alert-container .alert');
            alertElements.forEach(function(alert) {
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 1000);
            });
        }, 3000);
    });
</script>

<script>
    // Modified initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Get the approved button and panel
        const approvedButton = document.querySelector('[onclick="showHistory(\'approved\')"]');
        const approvedPanel = document.getElementById('approvedHistory');

        // Set initial state
        approvedButton.classList.add('active');
        approvedPanel.classList.remove('d-none');

        // Hide other panels explicitly
        document.querySelectorAll('.history-panel:not(#approvedHistory)').forEach(panel => {
            panel.classList.add('d-none');
        });
    });

    function showHistory(type) {
        // Remove active class from all buttons
        document.querySelectorAll('.btn-history-type').forEach(btn => {
            btn.classList.remove('active');
        });

        // Hide all panels
        document.querySelectorAll('.history-panel').forEach(panel => {
            panel.classList.add('d-none');
        });

        // Add active class to clicked button
        event.target.classList.add('active');
        
        // Show selected panel
        document.getElementById(`${type}History`).classList.remove('d-none');
    }
</script>


<script>
    // Color palette for different halls
    const HALL_COLORS = [
        '#4CAF50', '#2196F3', '#9C27B0', 
        '#FF9800', '#E91E63'
    ];

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.calendar-day[data-reserved="true"]').forEach(day => {
            day.addEventListener('click', function() {
                const date = this.getAttribute('data-date');
                showReservationDetails(date);
            });
        });
    });

    function showReservationDetails(date) {
        document.getElementById('selectedDate').textContent = 
            new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

        fetch(`/api/reservations/${date}`)
            .then(response => response.json())
            .then(reservations => {
                const timetableBody = document.getElementById('timetableBody');
                clearTimetable(timetableBody);

                // Create color mapping for halls
                const hallColorMap = createHallColorMap(reservations);

                reservations.forEach(reservation => {
                    const color = hallColorMap[reservation.hall_name];
                    processReservation(reservation, color, timetableBody);
                });

                new bootstrap.Modal(document.getElementById('reservationDetailModal')).show();
            })
            .catch(error => console.error('Error:', error));
    }

    function createHallColorMap(reservations) {
        const uniqueHalls = [...new Set(reservations.map(r => r.hall_name))];
        return uniqueHalls.reduce((map, hall, index) => {
            map[hall] = HALL_COLORS[index % HALL_COLORS.length];
            return map;
        }, {});
    }

    function processReservation(reservation, color, timetableBody) {
        const startDecimal = getHoursFromTime(reservation.start_time);
        const endDecimal = getHoursFromTime(reservation.end_time);
        const startHour = Math.floor(startDecimal);
        const endHour = Math.ceil(endDecimal);

        let detailsAdded = false;

        for (let hour = startHour; hour < endHour; hour++) {
            const slot = timetableBody.querySelector(`.reservation-slot[data-hour="${hour}"]`);
            if (slot) {
                slot.style.backgroundColor = color;
                
                // Add details only once per reservation in the first hour slot
                if (!detailsAdded && hour === startHour) {
                    const details = createReservationDetails(reservation, color);
                    slot.appendChild(details);
                    detailsAdded = true;
                }
            }
        }
    }

    function createReservationDetails(reservation, color) {
        const details = document.createElement('div');
        details.className = 'reservation-details small mb-1 p-1 rounded';
        details.style.backgroundColor = color.replace(/[^,]+\)/, '1)'); // Add opacity
        details.style.border = `1px solid ${color}`;
        details.innerHTML = `
            <strong>${reservation.hall_name}</strong><br>
            ${reservation.course_code}<br>
            ${formatTime(reservation.start_time)} - ${formatTime(reservation.end_time)}
        `;
        return details;
    }

    function clearTimetable(timetableBody) {
        timetableBody.querySelectorAll('.reservation-slot').forEach(td => {
            td.style.backgroundColor = '';
            td.innerHTML = '';
        });
    }

    // Helper function to convert time string to decimal hours
    function getHoursFromTime(timeStr) {
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours + (minutes / 60);
    }

    // Helper function to format time as HH:MM AM/PM
    function formatTime(timeStr) {
        const [hours, minutes] = timeStr.split(':');
        const date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
</script>


<script>
    let currentHallId = null;
    let currentHallDate = new Date();
    let isTransitioning = false;

    async function showHallCalendar(hallId, hallName) {
        try {
            currentHallId = hallId;
            currentHallDate = new Date();
            document.getElementById('currentHallName').textContent = hallName;
            await generateHallCalendar();
            initCalendarControls();
            new bootstrap.Modal(document.getElementById('hallCalendarModal')).show();
        } catch (error) {
            console.error('Error showing hall panel:', error);
        }
    }

    // Keep your existing generateHallCalendar function from the best version
    async function generateHallCalendar() {
        const container = document.getElementById('hallCalendarGrid');
        container.innerHTML = '';
        
        document.getElementById('currentMonthYear').textContent = 
            currentHallDate.toLocaleString('default', { month: 'long', year: 'numeric' });

        const startDate = new Date(Date.UTC(
            currentHallDate.getFullYear(), 
            currentHallDate.getMonth(), 
            1
        ));
        
        const endDate = new Date(Date.UTC(
            currentHallDate.getFullYear(), 
            currentHallDate.getMonth() + 1, 
            0
        ));

        const response = await fetch(
            `/api/hall-reservations/${currentHallId}?start=${startDate.toISOString()}&end=${endDate.toISOString()}`
        );
        const reservations = await response.json();
        const reservedDates = reservations.map(r => 
            new Date(r.date).toISOString().split('T')[0]
        );

        let dateCursor = new Date(startDate);
        dateCursor.setUTCDate(1 - dateCursor.getUTCDay());
        
        const headerRow = document.createElement('div');
        headerRow.className = 'calendar-row';
        ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].forEach(day => {
            headerRow.innerHTML += `<div class="calendar-weekday">${day}</div>`;
        });
        container.appendChild(headerRow);

        const endCursor = new Date(endDate);
        endCursor.setUTCDate(endCursor.getUTCDate() + (6 - endCursor.getUTCDay()));
        
        while(dateCursor <= endCursor) {
            const weekRow = document.createElement('div');
            weekRow.className = 'calendar-row';
            
            for(let i = 0; i < 7; i++) {
                const isCurrentMonth = dateCursor.getUTCMonth() === currentHallDate.getUTCMonth();
                const dateString = dateCursor.toISOString().split('T')[0];
                const isReserved = reservedDates.includes(dateString);
                
                const dayElement = document.createElement('div');
                dayElement.className = `calendar-day 
                    ${!isCurrentMonth ? 'other-month' : ''} 
                    ${isReserved ? 'reserved-day' : ''}`;
                dayElement.textContent = dateCursor.getUTCDate();
                
                if(isReserved) {
                    dayElement.style.cursor = 'pointer';
                    dayElement.addEventListener('click', () => loadDynamicContent(dateString));
                }
                
                weekRow.appendChild(dayElement);
                dateCursor.setUTCDate(dateCursor.getUTCDate() + 1);
            }
            container.appendChild(weekRow);
        }
    }

    // Keep your existing shiftHallMonth function with animations
    async function shiftHallMonth(offset) {
        if (isTransitioning) return;
        isTransitioning = true;
        
        const container = document.getElementById('hallCalendarGrid');
        container.classList.add('fade-out');
        await new Promise(resolve => setTimeout(resolve, 300));
        
        currentHallDate = new Date(Date.UTC(
            currentHallDate.getUTCFullYear(),
            currentHallDate.getUTCMonth() + offset,
            1
        ));
        
        await generateHallCalendar();
        container.classList.remove('fade-out');
        isTransitioning = false;
    }

    function initCalendarControls() {
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
    }

    function applyFilters() {
        const morningChecked = document.getElementById('filterMorning').checked;
        const afternoonChecked = document.getElementById('filterAfternoon').checked;
        const equipmentChecked = document.getElementById('filterEquipment').checked;
        
        // Implement your filter logic here
        console.log('Applying filters:', {
            morning: morningChecked,
            afternoon: afternoonChecked,
            equipment: equipmentChecked
        });
    }

    // Keep your existing loadDynamicContent function
    async function loadDynamicContent(date) {
        try {
            const loader = document.getElementById('contentLoader');
            const content = document.getElementById('dynamicContent');
            
            loader.style.display = 'block';
            content.innerHTML = '';
            
            const response = await fetch(`/api/hall-details/${currentHallId}?date=${date}`);
            const data = await response.json();
            
            content.innerHTML = `
                <h4>${new Date(date).toLocaleDateString()}</h4>
                <div class="reservation-list">
                    ${data.reservations.map(r => `
                        <div class="reservation-item mb-2 p-2 rounded">
                            <strong>${r.time}</strong> - ${r.course_code}<br>
                            ${r.instructor} - ${r.equipment || 'No equipment'}
                        </div>
                    `).join('')}
                </div>
            `;
            
            loader.style.display = 'none';
        } catch (error) {
            console.error('Error loading content:', error);
            loader.style.display = 'none';
            content.innerHTML = '<div class="alert alert-danger">Error loading content</div>';
        }
    }
</script>


