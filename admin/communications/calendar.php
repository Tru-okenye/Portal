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
        editable: true,
        selectable: true,
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('load_events.php') // Adjusted path
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
        dateClick: function(info) {
            var eventTitle = prompt('Enter Event Title:');
            if (eventTitle) {
                fetch('https://ikigaicollege.ac.ke/Portal/admin/communications/save_event.php', { // Adjusted path
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: eventTitle,
                        start: info.dateStr,
                        allDay: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendar.addEvent({
                            title: eventTitle,
                            start: info.dateStr,
                            allDay: true,
                            id: data.id // Include the ID returned from the server
                        });
                    } else {
                        alert('Error saving event');
                        console.error('Error saving event:', data.error); // Debugging line
                    }
                })
                .catch(error => {
                    console.error('Error saving event:', error); // Debugging line
                });
            }
        },
        eventClick: function(info) {
            var action = prompt('Edit or Delete Event? (Enter "edit" or "delete")');
            if (action === 'edit') {
                var newTitle = prompt('Edit Event Title:', info.event.title);
                if (newTitle) {
                    fetch('https://ikigaicollege.ac.ke/Portal/admin/communications/update_event.php', { // Adjusted path
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: info.event.id,
                            title: newTitle
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            info.event.setProp('title', newTitle);
                        } else {
                            alert('Error updating event');
                            console.error('Error updating event:', data.error); // Debugging line
                        }
                    })
                    .catch(error => {
                        console.error('Error updating event:', error); // Debugging line
                    });
                }
            } else if (action === 'delete') {
                if (confirm('Are you sure you want to delete this event?')) {
                    fetch('https://ikigaicollege.ac.ke/Portal/admin/communications/delete_event.php', { // Adjusted path
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: info.event.id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            info.event.remove();
                        } else {
                            alert('Error deleting event');
                            console.error('Error deleting event:', data.error); // Debugging line
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting event:', error); // Debugging line
                    });
                }
            }
        }
    });

    calendar.render();
});
</script>
