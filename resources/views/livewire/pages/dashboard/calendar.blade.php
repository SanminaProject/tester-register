<div wire:ignore class="flex flex-col h-full">
    <style>
        /* Mobile-only Calendar Optimization */
        @media (max-width: 639px) {
            .fc .fc-header-toolbar {
                flex-direction: row !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                column-gap: 16px !important;
                row-gap: 8px !important;
                margin-bottom: 0.5rem !important;
            }
            .fc .fc-toolbar-chunk {
                display: flex !important;
            }
            /* Row 1: Title centered */
            .fc .fc-toolbar-chunk:nth-child(2) {
                order: 1;
                width: 100%;
                justify-content: center;
                margin-bottom: 4px;
            }
            .fc .fc-toolbar-title {
                font-size: 1rem !important; 
            }
            /* Row 2 Left: Prev/Next/Today buttons */
            .fc .fc-toolbar-chunk:first-child {
                order: 2;
            }
            /* Use display contents to break out the custom filters and view buttons */
            .fc .fc-toolbar-chunk:last-child {
                display: contents !important;
            }
            /* Row 2 Right: The Month/Week/Day buttons */
            .fc .fc-toolbar-chunk:last-child > .fc-button-group {
                order: 3;
                display: flex !important;
            }
            /* Row 3: The JS injected filter checkboxes wrapper */
            .fc .fc-toolbar-chunk:last-child > div:first-child {
                order: 4;
                width: 100%;
                justify-content: center !important;
                gap: 2rem !important;
                padding: 4px 8px !important;
                margin-top: 4px;
            }
            /* Compress buttons to prevent wrapping on very small screens */
            .fc .fc-button {
                font-size: 0.7rem !important;
                padding: 0.3rem 0.5rem !important;
                text-transform: capitalize;
            }
            .fc-direction-ltr .fc-toolbar-chunk:first-child > .fc-button-group {
                margin-right: 0.25rem !important;
            }
            /* Adjust the grid cell vertical height */
            .fc .fc-daygrid-day-frame {
                min-height: 70px !important;
            }
            /* Prevent internal calendar scrolling on mobile */
            .fc .fc-view-harness {
                min-height: 350px !important;
                height: auto !important;
            }
            .fc .fc-view-harness-active > .fc-view {
                position: relative !important;
            }
            .fc .fc-scroller-liquid-absolute {
                position: relative !important;
                bottom: auto !important;
            }
            .fc .fc-scroller {
                height: auto !important;
                overflow-y: visible !important;
            }
        }
    </style>

    <div class="bg-white overflow-hidden shadow-sm shadow-sm border border-gray-100 sm:border-0 rounded-xl mb-4 sm:mb-0">
        <div class="p-2 sm:p-6 text-gray-900">
            <div 
                id="calendar" 
                data-events='@json($events)'
                class="form-control min-h-[350px] sm:min-h-[400px]">
            </div>
        </div>
    </div>
</div>