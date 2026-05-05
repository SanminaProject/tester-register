//./tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {

                primary: '#B10530',
                secondary: '#E18BA1',
                thirdly: '#F6E6E7',
                highlight: '#8BB6FF',
                
                'light-grey': '#F6F3F3',
                grey: '#D9D9D9',
                'dark-grey': '#7D7D7D',
                
                // M/C event colors
                'maintenance-bg': '#fef9c3',
                'maintenance-text': '#a16207',
                'maintenance-border': '#f87171',
                'maintenance-tag-bg': '#fee2e2',
                'maintenance-tag-text': '#991b1b',
                
                'calibration-bg': '#dbeafe',
                'calibration-text': '#1d4ed8',
                'calibration-border': '#60a5fa',
                'calibration-tag-bg': '#dbeafe',
                'calibration-tag-text': '#1e3a8a',
                
                'issue-bg': '#fee2e2',
                'issue-text': '#b91c1c',
                
                // Status colors
                'status-completed-bg': '#dcfce7',
                'status-completed-text': '#166534',
                'status-overdue-bg': '#fee2e2',
                'status-overdue-text': '#991b1b',
                'status-scheduled-bg': '#fef3c7',
                'status-scheduled-text': '#92400e',
                
                white: '#FFFFFF',
                black: '#000000',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
    safelist: [
        'bg-issue-bg',
        'text-issue-text',
        'bg-maintenance-bg',
        'text-maintenance-text',
        'bg-calibration-bg',
        'text-calibration-text',
        'bg-maintenance-tag-bg',
        'text-maintenance-tag-text',
        'bg-calibration-tag-bg',
        'text-calibration-tag-text',
        'bg-status-completed-bg',
        'text-status-completed-text',
        'bg-status-overdue-bg',
        'text-status-overdue-text',
        'bg-status-scheduled-bg',
        'text-status-scheduled-text',
        'bg-gray-100',
        'text-gray-700',
    ],
};
