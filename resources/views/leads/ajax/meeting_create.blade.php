<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.meeting.newMeeting')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<style>
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        border-radius: 15px 15px 0 0;
        padding: 25px 30px 20px;
    }

    .modal-header .modal-title {
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.5rem;
    }

    .modal-header .close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #7f8c8d;
        opacity: 1;
        transition: color 0.3s ease;
    }

    .modal-header .close:hover {
        color: #e74c3c;
    }

    .modal-body {
        background: #fff;
        padding: 0px 30px 30px;
    }

    .modal-footer {
        background: #fff;
        border-top: 1px solid #f0f0f0;
        border-radius: 0 0 15px 15px;
        padding: 20px 30px;
    }

    .clender {
        border-right: 2px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .time-table {
        display: flex;
        flex-direction: column;
    }

    .content-clender {
        height: 500px;
        display: grid;
        grid-template-columns: 50% 50%;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .main-clender {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        margin-top: 20px;
    }

    .calendar-container {
        background: #fff;
        width: 450px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .calendar-container header {
        display: flex;
        align-items: center;
        padding: 25px 30px 10px;
        justify-content: center;
    }

    header .calendar-navigation {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    header .calendar-navigation span {
        height: 38px;
        width: 38px;
        margin: 0 1px;
        cursor: pointer;
        text-align: center;
        line-height: 38px;
        border-radius: 50%;
        user-select: none;
        color: #7f8c8d;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .calendar-navigation span:last-child {
        margin-right: -10px;
    }

    header .calendar-navigation span:hover {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%) !important;
        color: #fff;
        transform: scale(1.1);
    }

    header .calendar-current-date {
        font-weight: 600;
        font-size: 1.45rem;
        color: #2c3e50;
        margin: 0 20px;
    }

    .calendar-body {
        padding: 20px;
    }

    .calendar-body ul {
        list-style: none;
        flex-wrap: wrap;
        display: flex;
        text-align: center;
    }

    .calendar-body .calendar-dates {
        margin-bottom: 20px;
    }

    .calendar-body li {
        width: calc(100% / 7);
        font-size: 1.07rem;
        color: #2c3e50;
    }

    .calendar-body .calendar-weekdays li {
        cursor: default;
        font-weight: 600;
        color: #7f8c8d;
        padding: 10px 0;
    }

    .calendar-body .calendar-dates li {
        margin-top: 30px;
        position: relative;
        z-index: 1;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .calendar-dates li.inactive {
        color: #bdc3c7;
        cursor: not-allowed;
    }

    .calendar-dates li.active {
        color: #fff;
    }

    .calendar-dates li::before {
        position: absolute;
        content: "";
        z-index: -1;
        top: 50%;
        left: 50%;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: all 0.3s ease;
    }

    .calendar-dates li.active::before {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%) !important;
    }

    .calendar-dates li:not(.active):not(.inactive):hover::before {
        background: rgba(102, 126, 234, 0.1);
        transform: translate(-50%, -50%) scale(1.1);
    }

    .calendar-dates li:not(.active):not(.inactive):hover {
        color: var(--gradientColor1);
        font-weight: 600;
    }

    .btn-group {
        gap: 0px !important;
    }

    .btn-group .btn {
        color: #2c3e50;
        border: 2px solid #ecf0f1;
        background: #fff;
        font-weight: 500;
        padding: 5px 8px;
        border-radius: 8px;
        margin: 0 5px;
        transition: all 0.3s ease;
    }

    .btn-group .btn:hover {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%) !important;
        color: #fff;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-group .btn.active {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%) !important;
        color: #fff;
        border-color: transparent;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .time-table h3 {
        font-size: 18px;
        margin-top: 25px;
        color: #2c3e50;
        font-weight: 600;
    }

    .time-table p {
        margin: 0px;
        color: #7f8c8d;
    }

    .time-table {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 0px 25px;
        align-items: start;
    }

    .cards {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-content {
        height: 50px;
        width: 200px;
        border: 2px solid #ecf0f1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff;
        color: #2c3e50;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .card-content:hover {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%) !important;
        color: #fff;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .active-time-slot {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%) !important;
        font-weight: bold;
        border: 2px solid transparent !important;
        color: #fff !important;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
        border: 2px solid #ecf0f1;
        border-radius: 8px;
        padding: 15px;
        transition: all 0.3s ease;
        background: #fff;
        color: #2c3e50;
    }

    textarea.form-control:focus {
        border-color: var(--gradientColor1);
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
    }

    .form-control {
        border: 2px solid #ecf0f1;
        border-radius: 8px;
        padding: 12px 15px;
        transition: all 0.3s ease;
        background: #fff;
        color: #2c3e50;
    }

    .form-control:focus {
        border-color: var(--gradientColor1);
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
    }

    .form-label {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
    }

    /* Modal Footer Buttons */
    .modal-footer .btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        margin: 0 5px;
    }

    .modal-footer .btn-cancel {
        background: #ecf0f1;
        color: #7f8c8d;
    }

    .modal-footer .btn-cancel:hover {
        background: #d5dbdb;
        color: #2c3e50;
        transform: translateY(-1px);
    }

    .modal-footer .btn-primary {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%) !important;
        color: #fff;
        border: none;
    }

    .modal-footer .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .list {
        max-height: 250px;
        width: 100%;
        overflow-y: auto;
        padding-right: 5px;
    }

    .list::-webkit-scrollbar {
        width: 6px;
    }

    .list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .list::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, var(--gradientColor1) -0.06%, var(--gradientColor2) 33.96%, var(--gradientColor3) 72.98%, var(--gradientColor4) 100%);
        border-radius: 10px;
    }

    .list::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, var(--gradientColor2) -0.06%, var(--gradientColor3) 33.96%, var(--gradientColor4) 72.98%, var(--gradientColor1) 100%);
    }

    /* Timezone indicator styling */
    .timezone-info {
        color: var(--gradientColor1);
        font-weight: 600;
        padding: 8px 12px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 6px;
        border-left: 4px solid var(--gradientColor1);
    }
</style>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="createMeetingForm" method="POST" class="ajax-form">
            <div class="form-body">
                <div class="content-clender">
                    <div class="clender">
                        <div class="calendar-container">
                            <header class="calendar-header">
                                <div class="calendar-navigation">
                                    <span id="calendar-prev" class="material-symbols-rounded">
                                        < </span>
                                            <p class="calendar-current-date" style=" padding-top: 10px;"></p>
                                            <span id="calendar-next" class="material-symbols-rounded">
                                                >
                                            </span>
                                </div>
                            </header>
                            <input type="hidden" name="today_data" value="{{ date('d-m-Y') }}">

                            <div class="calendar-body">
                                <ul class="calendar-weekdays">
                                    <li>Sun</li>
                                    <li>Mon</li>
                                    <li>Tue</li>
                                    <li>Wed</li>
                                    <li>Thu</li>
                                    <li>Fri</li>
                                    <li>Sat</li>
                                </ul>
                                <ul class="calendar-dates"></ul>
                            </div>
                        </div>
                    </div>

                    <div class="time-table">
                        <h3>How long do you need?</h3>
                        <div class="btn-group" role="group" aria-label="Basic outlined example">
                            <button type="button" data-mint="60" class="sbtn btn btn-outline-primary">1 Hour</button>
                            <button type="button" data-mint="45" class="sbtn btn btn-outline-primary">45 mins</button>
                            <button type="button" data-mint="30" class="sbtn btn btn-outline-primary">30 mins</button>
                            <button type="button" data-mint="15" class="sbtn btn btn-outline-primary active">15
                                mins</button>
                        </div>
                        <h3>Choose a time</h3>
                        <div>
                            <p>Showing times for <b class="dat">{{ (new \DateTime())->format('F j, Y') }}</b></p>
                        </div>
                        <div style="padding: 10px 0px;">
                            <div class="timezone-info">Asia/Karachi (UTC +05:00)</div>
                        </div>
                        <div class="list">
                            @foreach ($slots as $slot)
                                <div class="card-content ti" data-time="{{ $slot }}"
                                    style="padding: 12px 24px; margin-bottom: 6px; width: 100%;">
                                    {{ $slot }}</div>
                            @endforeach
                        </div>

                        <!-- Added Description Field -->
                        <input type="hidden" name="time" id="time" value="">
                        <input type="hidden" name="mint" id="mint" value="15">
                        <input type="hidden" name="date" id="date" value="{{ date('d-m-Y') }}">
                        <input type="hidden" name="lead_id" value="{{ $leadId }}">
                    </div>
                </div>
                {{-- <div class="col-md-12"> --}}
                <div class="form-group">
                    {{ Form::label('cohost_email', __('Cohost Email'), ['class' => 'form-label']) }}
                    {{-- {{ Form::text('cohost_email', 'uzairaftab332211@gmail.com', ['class' => 'form-control', 'placeholder' => __('Enter Cohost Email')]) }} --}}
                    {{ Form::text('cohost_email', '33384@iqraisb.edu.pk', ['class' => 'form-control', 'placeholder' => __('Enter Cohost Email')]) }}
                    @error('cohost_email')
                        <span class="invalid-cohost_email" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                {{-- </div> --}}
                <div class="form-group mt-3">
                    <h3>Meeting Description</h3>
                    <textarea class="form-control" name="description" id="description" rows="3"
                        placeholder="Enter meeting description..."></textarea>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal"
        class="btn-cancel border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-meeting" class="btn-primary"
        icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    var date1 = new Date();
    var year = date1.getFullYear();
    var month = date1.getMonth();

    var day = document.querySelector(".calendar-dates");
    var currdate = document.querySelector(".calendar-current-date");
    var prenexIcons = document.querySelectorAll(".calendar-navigation span");

    var months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
    ];

    var manipulate = () => {
        var firstDayOfMonth = new Date(year, month, 1).getDay();
        var lastDateOfMonth = new Date(year, month + 1, 0).getDate();
        var lastDayOfMonth = new Date(year, month, lastDateOfMonth).getDay();
        var lastDateOfPreviousMonth = new Date(year, month, 0).getDate();

        var lit = "";

        // Adding dates of the previous month
        for (var i = firstDayOfMonth; i > 0; i--) {
            var dayNum = lastDateOfPreviousMonth - i + 1;
            var prevMonthYear = month === 0 ? year - 1 : year;
            var prevMonth = month === 0 ? 12 : month;
            lit += `<li class="inactive" data-date="${dayNum}-${prevMonth}-${prevMonthYear}">${dayNum}</li>`;
        }

        // Adding dates of the current month
        for (var i = 1; i <= lastDateOfMonth; i++) {
            var today = new Date();
            var todayDay = today.getDate();
            var todayMonth = today.getMonth();
            var todayYear = today.getFullYear();
            var dateClass = "";
            if (year < todayYear ||
                (year === todayYear && month < todayMonth) ||
                (year === todayYear && month === todayMonth && i < todayDay)) {
                dateClass = "inactive";
            } else if (i === todayDay && month === todayMonth && year === todayYear) {
                dateClass = "active";
            }
            lit +=
                `<li class="${dateClass}" data-date="${i}-${month + 1}-${year}" ${dateClass === "inactive" ? 'disabled' : ''}>${i}</li>`;
        }

        // Adding dates of the next month
        for (var i = lastDayOfMonth + 1; i < 7; i++) {
            var dayNum = i - lastDayOfMonth;
            var nextMonthYear = month === 11 ? year + 1 : year;
            var nextMonth = month === 11 ? 1 : month + 2;
            lit += `<li class="inactive" data-date="${dayNum}-${nextMonth}-${nextMonthYear}">${dayNum}</li>`;
        }

        currdate.innerText = `${months[month]} ${year}`;
        day.innerHTML = lit;
        var today = new Date();
        var todayMonth = today.getMonth();
        var todayYear = today.getFullYear();
        var nextMonth = todayMonth === 11 ? 0 : todayMonth + 1;
        var nextYear = todayMonth === 11 ? todayYear + 1 : todayYear;
        // Disable navigation icons based on the month
        prenexIcons.forEach(icon => {
            if (icon.id === "calendar-prev") {
                // Disable "previous" icon if we are viewing the current month
                icon.style.pointerEvents = (year === todayYear && month === todayMonth) ? 'none' : 'auto';
                icon.style.opacity = (year === todayYear && month === todayMonth) ? '0.3' : '1';
            } else if (icon.id === "calendar-next") {
                // Disable "next" icon if we are in one month next;
                var isNextMonth = (year === nextYear && month === nextMonth);
                icon.style.pointerEvents = isNextMonth ? 'none' : 'auto';
                icon.style.opacity = isNextMonth ? '0.3' : '1';
            }
        });

        // Reattach click event handlers after updating the DOM
        $('.calendar-dates li').off('click').on('click', function() {
            if ($(this).hasClass('inactive')) {
                return;
            }
            $('.calendar-dates li').removeClass('active'); // Remove 'active' from all
            $(this).addClass('active'); // Add 'active' to the clicked date
            // Retrieve and format the selected date
            var date = $(this).data('date');
            $('#date').val(date);
            fetchAvailableTime(date);
            // Create a Date object from the retrieved date
            var [day, month, year] = date.split('-');
            var formattedDate = new Date(year, month - 1, day);

            // Format the date
            var optionsDate = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: 'Asia/Karachi'
            };
            var formattedDateStr = formattedDate.toLocaleDateString('en-US', optionsDate);

            var optionsDate2 = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: 'Asia/Karachi'
            };
            var formattedDateStr2 = new Intl.DateTimeFormat('en-US', optionsDate2).format(formattedDate);

            // Format the time
            var optionsTime = {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true,
                timeZone: 'Asia/Karachi'
            };
            var formattedTime = formattedDate.toLocaleTimeString('en-US', optionsTime);

            // Combine date and time
            var formattedDateTime = `${formattedDateStr},`;
            // var formateTime = ` ${formattedTime},`;

            // Set the formatted date and time to the element
            $('.meet_time').empty().append('You are currently scheduled to meet on ' + formattedDateTime);
            $('.dat').empty().append(formattedDateStr2);
            // $('.ti_op').empty().append(formateTime);
        });
    }

    manipulate();

    prenexIcons.forEach(icon => {
        icon.addEventListener("click", () => {
            if (icon.style.pointerEvents === 'none') return;

            if (icon.id === "calendar-prev") {
                month--;
                if (month < 0) {
                    month = 11;
                    year--;
                }
            } else if (icon.id === "calendar-next") {
                month++;
                if (month > 11) {
                    month = 0;
                    year++;
                }
            }

            // Update the date object to reflect the new month and year
            date = new Date(year, month);
            manipulate();
        });
    });

    function fetchAvailableTime(date) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var time = $('#mint').val();

        $.ajax({
            url: "{{ route('available-time') }}",
            method: 'POST',
            data: {
                date: date,
                time: time
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Custom-Header': 'CustomValue'
            },
            success(response) {
                if (response.status === 'success') {
                    var slotElements = response.data.map(timeSlot => `
                            <div class="card-content ti" data-time="${timeSlot}"
                                style="padding: 12px 24px; margin-bottom: 6px; width: 100%;">
                                ${timeSlot}
                            </div>
                        `).join('');

                    $('.list').empty().append(slotElements);
                }
            },
            error(xhr) {
                alert(`An error occurred: ${xhr.responseText}`);
            }
        });
    }

    $(document).ready(function() {
        $('.sbtn').on('click', function() {
            $('.sbtn').removeClass('active'); // Remove 'active' from all buttons
            $(this).addClass('active'); // Add 'active' to the clicked button
            $('#mint').val($(this).data('mint')); // Store the selected minutes
            var date = $('#date').val();
            fetchAvailableTime(date);
        });

        // Modified time slot click handler - only selects the time slot but doesn't submit the form
        $(document).on("click", ".ti", function() {
            $('.ti').removeClass('active-time-slot'); // Remove highlight from all time slots
            $(this).addClass('active-time-slot'); // Highlight the selected time slot
            var time = $(this).data('time');
            $('#time').val(time);
            $('.ti_op').empty().append(time);
            // Form submission is now handled by the Create button in the modal footer
        });
        /* Add this to the top of your style section - missing gradient variables */

        $('#save-meeting').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Stop event bubbling

            var time = $('#time').val();
            var date = $('#date').val();

            if (!time) {
                alert('Please select a time slot for the meeting.');
                return false;
            }

            // Disable the button to prevent multiple clicks
            $(this).prop('disabled', true);

            $.ajax({
                url: "{{ route('google.create.meet') }}",
                type: "POST",
                data: $('#createMeetingForm').serialize(),
                success: function(response) {
                    if (response.status == "success") {
                        if (response.redirect) {
                            // Use setTimeout to ensure the response is processed
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 100);
                        } else {
                            window.location.reload();
                        }
                    }
                },
                error: function(xhr) {
                    var response = xhr.responseJSON;
                    if (response && response.redirect) {
                        // Immediate redirect for authentication
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 100);
                    } else {
                        $('#save-meeting').prop('disabled', false); // Re-enable button
                        if (response && response.message) {
                            alert(response.message);
                        } else {
                            alert('An error occurred while creating the meeting.');
                        }
                    }
                }
            });
        });
        // Also add validation to prevent form submission without required fields
        $('#createMeetingForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            var time = $('#time').val();
            if (!time) {
                alert('Please select a time slot for the meeting.');
                return false;
            }

            // Trigger the save button click
            $('#save-meeting').click();
        });
    });
</script>
