<style>
      
        h1 {
            color: #E39825;
        }
        </style>
<h1>Event Calendar</h1>

<div id="calendar"></div> <!-- This is where the calendar will be rendered -->

<!-- FullCalendar CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/fullcalendar.min.css">
<link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/calendar.css"> 

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<!-- jQuery (if needed by FullCalendar for version) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom JS for the calendar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        editable: false, // Disable editing
        selectable: false, // Disable selection for adding events
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('https://ikigaicollege.ac.ke/Portal/admin/communications/load_events.php') // Adjusted path
                .then(response => response.json())
                .then(data => {
                    console.log('Fetched events:', data); // Debugging line
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching events:', error); // Debugging line
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            alert('Event: ' + info.event.title + '\nDate: ' + info.event.start.toLocaleDateString()); // Display event details on click
        }
    });

    calendar.render();
});

</script>
