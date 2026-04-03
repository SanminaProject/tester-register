import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

// Initialize the calendar used in dashboard
document.addEventListener('calendar-ready', function () {
    const calendarEl = document.getElementById('calendar')
    // const events = JSON.parse(calendarEl.dataset.events)

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],

        initialView: 'dayGridMonth', // monthly view by default

        headerToolbar: {
            left: 'prev,next,today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        },

        events: function(fetchInfo, successCallback, failureCallback) {
            console.log('Fetching calendar events...');
            fetch('/calendar-events')
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Events data:', data);
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },

        eventClassNames: function(arg) {
            return [arg.event.extendedProps.type];
        },

        dayMaxEvents: 3,
        dayMaxEventRows: true,

        eventClick: function(info) {
            console.log('Event clicked:', info.event);
        },

        eventMouseEnter: function(info) {
            console.log('Hover start:', info.event);
        },

        eventMouseLeave: function(info) {
            console.log('Hover end:', info.event);
        }
    })

    calendar.render()
})