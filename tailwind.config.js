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
        'bg-red-100',
        'text-red-700',
        'bg-yellow-100',
        'text-yellow-700',
        'bg-blue-100',
        'text-blue-700',
        'bg-gray-100',
        'text-gray-700',
    ],
};
