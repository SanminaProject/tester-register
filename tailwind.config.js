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
                
                'maintenance-bg': '#fef9c3',
                'maintenance-text': '#a16207',
                'calibration-bg': '#dbeafe',
                'calibration-text': '#1d4ed8',
                'issue-bg': '#fee2e2',
                'issue-text': '#b91c1c',
                
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
        'bg-gray-100',
        'text-gray-700',
    ],
};
