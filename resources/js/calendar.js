import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

// Initialize the calendar used in dashboard
document.addEventListener('DOMContentLoaded', function () {
    console.log('Calendar JS loaded')
    
    const calendarEl = document.getElementById('calendar')

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],

        initialView: 'dayGridMonth', // monthly view by default

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        events: [
            { title: 'Meeting', start: new Date().toISOString() }
        ]
    })

    calendar.render()
})