import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

// Initialize the calendar used in dashboard
document.addEventListener('calendar-ready', function () {
    const calendarEl = document.getElementById('calendar');
    
    if (!calendarEl) return;
    calendarEl.innerHTML = '';
    
    let events = [];
    try {
        events = JSON.parse(calendarEl.dataset.events || '[]');
    } catch(e) {
        console.error('Failed to parse calendar events', e);
    }

    const formatEventTime = (date) => {
        if (!date) return '-';

        return new Intl.DateTimeFormat(undefined, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).format(date);
    };

    const formatEventDateTime = (date) => {
        if (!date) return '-';

        const pad = (n) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };

    const getEventSummary = (event) => {
        const eventId = event.extendedProps.event_code || event.id || '-';
        const date = formatEventDateTime(event.start);
        const testerId = event.extendedProps.tester_id ?? '-';
        const testerName = event.extendedProps.tester_name || `Tester #${event.extendedProps.tester_id || event._def?.publicId || '-'}`;
        const typeLabel = event.extendedProps.maintenance_calibration || (event.extendedProps.type === 'calibration' ? 'Calibration' : 'Maintenance');
        const userName = (event.extendedProps.user_name || '').trim() || 'Unassigned';
        const status = (event.extendedProps.event_status || '').trim() || 'Unknown';
        const lastDate = event.extendedProps.last_date ? formatEventDateTime(new Date(event.extendedProps.last_date)) : null;
        const nextDate = event.extendedProps.next_date ? formatEventDateTime(new Date(event.extendedProps.next_date)) : null;
        const time = formatEventTime(event.start);

        return {
            eventId,
            date,
            testerId,
            testerName,
            typeLabel,
            userName,
            status,
            lastDate,
            nextDate,
            time,
            line: `Event ID: ${eventId}  Tester: ${testerName}  Status: ${status}  Time: ${time}`,
        };
    };

    const openEventPopup = (event) => {
        const existing = document.getElementById('calendar-event-popup');
        if (existing) existing.remove();

        const summary = getEventSummary(event);
        const overlay = document.createElement('div');
        overlay.id = 'calendar-event-popup';
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.background = 'rgba(0,0,0,0.28)';
        overlay.style.zIndex = '9999';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.padding = '16px';

        const modal = document.createElement('div');
        modal.style.width = 'min(520px, 100%)';
        modal.style.background = '#fff';
        modal.style.borderRadius = '12px';
        modal.style.padding = '16px 18px';
        modal.style.boxShadow = '0 12px 30px rgba(0,0,0,0.18)';

        const title = document.createElement('div');
        title.textContent = 'Event Details';
        title.style.fontSize = '16px';
        title.style.fontWeight = '700';
        title.style.marginBottom = '10px';

        const body = document.createElement('div');
        body.style.display = 'grid';
        body.style.gridTemplateColumns = '110px 1fr';
        body.style.gap = '8px 10px';
        body.style.fontSize = '14px';
        body.style.color = '#1f2937';

        const pushRow = (label, value) => {
            const l = document.createElement('div');
            l.textContent = label;
            l.style.color = '#6b7280';
            const v = document.createElement('div');
            v.textContent = value;
            v.style.wordBreak = 'break-word';
            body.appendChild(l);
            body.appendChild(v);
        };

        pushRow('ID', String(summary.eventId));
        pushRow('Date', summary.date);
        pushRow('Tester ID', String(summary.testerId));
        pushRow('Tester Name', summary.testerName);
        pushRow('Type', summary.typeLabel);
        pushRow('User', summary.userName);
        pushRow('Status', summary.status);
        if (summary.lastDate) pushRow('Last', summary.lastDate);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.textContent = 'Close';
        closeBtn.style.marginTop = '14px';
        closeBtn.style.padding = '7px 14px';
        closeBtn.style.borderRadius = '999px';
        closeBtn.style.border = '1px solid #e5e7eb';
        closeBtn.style.background = '#f9fafb';
        closeBtn.style.cursor = 'pointer';

        const close = () => overlay.remove();
        closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close();
        });

        modal.appendChild(title);
        modal.appendChild(body);
        modal.appendChild(closeBtn);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
    };

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],

        initialView: calendarEl.dataset.view || 'dayGridMonth', // monthly view by default
        firstDay: 1, // Start week on Monday

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

        displayEventTime: false,

        events: events,

        eventContent: function(arg) {
            const summary = getEventSummary(arg.event);
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-center min-w-0 w-full rounded-lg shadow-sm overflow-hidden whitespace-nowrap';
            wrapper.style.boxSizing = 'border-box';
            wrapper.style.height = '100%';
            wrapper.style.backdropFilter = 'blur(4px)';

            // Get colors from CSS variables
            const root = getComputedStyle(document.documentElement);
            const isMaintenance = arg.event.extendedProps.maintenance_calibration === 'Maintenance';
            
            // Left part: M/C colored background with event ID
            const leftPart = document.createElement('div');
            leftPart.className = 'flex items-center px-2.5 py-1.5';
            leftPart.style.flex = '0 1 auto';
            leftPart.style.minWidth = '0';
            
            if (isMaintenance) {
                leftPart.style.background = root.getPropertyValue('--color-maintenance-bg').trim();
                leftPart.style.color = root.getPropertyValue('--color-maintenance-text').trim();
            } else {
                leftPart.style.background = root.getPropertyValue('--color-calibration-bg').trim();
                leftPart.style.color = root.getPropertyValue('--color-calibration-text').trim();
            }

            const idEl = document.createElement('div');
            idEl.className = 'text-[11px] font-semibold leading-tight whitespace-nowrap overflow-hidden text-ellipsis';
            idEl.style.minWidth = '0';
            idEl.textContent = summary.eventId;
            leftPart.appendChild(idEl);

            // Right part: white background with status badge
            const rightPart = document.createElement('div');
            rightPart.className = 'flex items-center px-2 py-1.5 ml-auto';
            rightPart.style.background = 'rgba(255,255,255,0.95)';
            rightPart.style.flex = '0 0 auto';

            const badge = document.createElement('span');
            badge.textContent = summary.status;
            badge.style.fontSize = '10px';
            badge.style.padding = '2px 8px';
            badge.style.borderRadius = '999px';
            badge.style.flex = '0 0 auto';
            badge.style.lineHeight = '1.2';
            badge.style.whiteSpace = 'nowrap';

            if (summary.status.toLowerCase() === 'completed') {
                badge.style.background = root.getPropertyValue('--color-status-completed-bg').trim();
                badge.style.color = root.getPropertyValue('--color-status-completed-text').trim();
            } else if (summary.status.toLowerCase() === 'overdue') {
                badge.style.background = root.getPropertyValue('--color-status-overdue-bg').trim();
                badge.style.color = root.getPropertyValue('--color-status-overdue-text').trim();
            } else {
                badge.style.background = root.getPropertyValue('--color-status-scheduled-bg').trim();
                badge.style.color = root.getPropertyValue('--color-status-scheduled-text').trim();
            }
            rightPart.appendChild(badge);

            wrapper.appendChild(leftPart);
            wrapper.appendChild(rightPart);

            return { domNodes: [wrapper] };
        },

        eventDidMount: function(arg) {
            arg.el.style.color = '#111827';
            arg.el.style.borderRadius = '12px';
            arg.el.style.boxShadow = '0 1px 2px rgba(15, 23, 42, 0.08)';
            const main = arg.el.querySelector('.fc-event-main, .fc-event-main-frame');
            if (main) {
                main.style.color = '#111827';
            }
            const frame = arg.el.querySelector('.fc-event-main-frame, .fc-event-main');
            if (frame) {
                frame.style.padding = '1px 0';
            }
        },

        eventClassNames: function(arg) {
            return [arg.event.extendedProps.type];
        },

        dayMaxEvents: 3,
        dayMaxEventRows: true,

        eventClick: function(info) {
            info.jsEvent.preventDefault();
            openEventPopup(info.event);
        },
    })

   calendar.render()

   // expose instance for later updates and listen for live update events
   calendarEl.__fc = calendar;

   document.addEventListener('calendar-update', function(e) {
       try {
           const fc = calendarEl.__fc;
           if (!fc) return;
           const events = (e.detail && e.detail.events) ? e.detail.events : [];
           // normalize: ensure dates are parsed
           fc.removeAllEvents();
           if (events.length > 0) fc.addEventSource(events);
       } catch (err) {
           console.error('Failed to update calendar events', err);
       }
   });

   // Relay tester-updated browser events into Livewire so other components (e.g., MaintenanceSettings) can react
   document.addEventListener('tester-updated', function(e) {
       try {
           const testerId = e.detail?.testerId ?? null;
           if (testerId && window.Livewire && typeof Livewire.emit === 'function') {
               Livewire.emit('testerUpdated', testerId);
           }
       } catch (err) {
           console.error('Failed to relay tester-updated to Livewire', err);
       }
   });

   const rightContainer = calendarEl.querySelector('.fc-header-toolbar .fc-toolbar-chunk:last-child');
   
   if (rightContainer) {
       if (rightContainer.querySelector('.calendar-filter-cb')) return;
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

   const checkboxes = calendarEl.querySelectorAll('.calendar-filter-cb');
   
   if (checkboxes.length > 0) {
       checkboxes.forEach(cb => {
           cb.addEventListener('change', function(e) {
               
               const checkedBoxes = calendarEl.querySelectorAll('.calendar-filter-cb:checked');
               

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
       const checkedValues = Array.from(calendarEl.querySelectorAll('.calendar-filter-cb:checked')).map(cb => cb.value);
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