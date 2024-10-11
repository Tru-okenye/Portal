document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        editable: true,
        selectable: true,
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('load_events.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Fetched events:', data); // Add this line to debug
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching events:', error); // Add this line to debug
                    failureCallback(error);
                });
        },
        

        dateClick: function(info) {
            var eventTitle = prompt('Enter Event Title:');
            if (eventTitle) {
                fetch('save_event.php', {
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
                    }
                });
            }
        },

        eventClick: function(info) {
            var action = prompt('Edit or Delete Event? (Enter "edit" or "delete")');
            if (action === 'edit') {
                var newTitle = prompt('Edit Event Title:', info.event.title);
                if (newTitle) {
                    fetch('update_event.php', {
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
                        }
                    });
                }
            } else if (action === 'delete') {
                if (confirm('Are you sure you want to delete this event?')) {
                    fetch('delete_event.php', {
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
                        }
                    });
                }
            }
        }
    });

    calendar.render();
});