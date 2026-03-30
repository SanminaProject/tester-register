import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

// Initialize the calendar used in dashboard
document.addEventListener('calendar-ready', function () {
    const calendarEl = document.getElementById('calendar')
    const events = JSON.parse(calendarEl.dataset.events)

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

        events: events,

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