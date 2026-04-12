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
    })

   calendar.render()

   const rightContainer = document.querySelector('.fc-header-toolbar .fc-toolbar-chunk:last-child');
   
   if (rightContainer) {
       rightContainer.style.display = 'flex';
       rightContainer.style.alignItems = 'center';
       rightContainer.style.gap = '1.5rem'; 

       const filterHTML = `
       <div class="flex items-center gap-4 bg-gray-50/50 px-3 py-1.5 rounded-lg border border-gray-100">
           <label class="flex items-center gap-2 cursor-pointer group">
               <input type="checkbox" value="maintenance" class="calendar-filter-cb w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" style="accent-color: #B10530;" checked>
               <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900 transition">Maintenance</span>
           </label>
           
           <label class="flex items-center gap-2 cursor-pointer group">
               <input type="checkbox" value="calibration" class="calendar-filter-cb w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" style="accent-color: #B10530;" checked>
               <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900 transition">Calibration</span>
           </label>
       </div>
   `;

       rightContainer.insertAdjacentHTML('afterbegin', filterHTML);
   }

   const checkboxes = document.querySelectorAll('.calendar-filter-cb');
   
   if (checkboxes.length > 0) {
       checkboxes.forEach(cb => {
           cb.addEventListener('change', function(e) {
               
               const checkedBoxes = document.querySelectorAll('.calendar-filter-cb:checked');
               

               if (checkedBoxes.length === 0) {
                   e.preventDefault(); 
                   this.checked = true; 
                   
                   console.log('必须至少选择一种类型！');
                   return; 
               }

               updateCalendarView();
           });
       });
   }

   function updateCalendarView() {
       const checkedValues = Array.from(document.querySelectorAll('.calendar-filter-cb:checked')).map(cb => cb.value);
       const allCalendarEvents = calendar.getEvents();

       allCalendarEvents.forEach(function(event) {
           const eventType = event.extendedProps.type;
           
           if (eventType === 'maintenance' || eventType === 'calibration') {
               if (checkedValues.includes(eventType)) {
                   event.setProp('display', 'auto'); 
               } else {
                   event.setProp('display', 'none'); 
               }
           } else {
               event.setProp('display', 'auto'); 
           }
       });
   }
})