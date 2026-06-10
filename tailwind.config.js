/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
        './resources/js/**/*.ts',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
                brand: {
                    green: '#00ed64',
                    'green-dark': '#00684a',
                    'green-mid': '#00a35c',
                    'green-soft': '#c3f0d2',
                    teal: '#003d4f',
                    'teal-deep': '#001e2b',
                    'teal-mid': '#00684a',
                },
                canvas: {
                    DEFAULT: '#ffffff',
                    dark: '#001e2b',
                },
                surface: {
                    DEFAULT: '#f9fbfa',
                    soft: '#f4f7f6',
                    feature: '#e3fcef',
                },
                ink: '#001e2b',
            },
            borderRadius: {
                lg: '12px',
                xl: '16px',
            }
        },
    },
    plugins: [require("tailwindcss-animate")],
};
