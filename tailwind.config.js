import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import { colors, typography, borderRadius, shadows } from './resources/js/utils/designTokens.js';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    
    darkMode: 'class',
    safelist: [
        'dark',
        'high-contrast',
        'text-size-small',
        'text-size-medium',
        'text-size-large',
        'reduced-motion',
    ],
    
    theme: {
        screens: {
            'xs': '475px',
            ...defaultTheme.screens,
        },
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            black: '#000',
            white: '#fff',
            red: {
                50: '#fef2f2',
                100: '#fee2e2',
                200: '#fecaca',
                300: '#fca5a5',
                400: '#f87171',
                500: '#ef4444',
                600: '#dc2626',
                700: '#b91c1c',
                800: '#991b1b',
                900: '#7f1d1d',
                950: '#450a0a',
            },
            primary: {
                50: 'var(--color-primary-50, #f0f9ff)',
                100: 'var(--color-primary-100, #e0f2fe)',
                200: 'var(--color-primary-200, #bae6fd)',
                300: 'var(--color-primary-300, #7dd3fc)',
                400: 'var(--color-primary-400, #38bdf8)',
                500: 'var(--color-primary-500, #0ea5e9)',
                600: 'var(--color-primary-600, #0284c7)',
                700: 'var(--color-primary-700, #0369a1)',
                800: 'var(--color-primary-800, #075985)',
                900: 'var(--color-primary-900, #0c4a6e)',
            },
            secondary: {
                50: 'var(--color-secondary-50, #faf5ff)',
                100: 'var(--color-secondary-100, #f3e8ff)',
                200: 'var(--color-secondary-200, #e9d5ff)',
                300: 'var(--color-secondary-300, #d8b4fe)',
                400: 'var(--color-secondary-400, #c084fc)',
                500: 'var(--color-secondary-500, #a855f7)',
                600: 'var(--color-secondary-600, #9333ea)',
                700: 'var(--color-secondary-700, #7e22ce)',
                800: 'var(--color-secondary-800, #6b21a8)',
                900: 'var(--color-secondary-900, #581c87)',
            },
            ...colors.neutral,
            neutral: colors.neutral,
            accent: colors.accent,
            success: 'var(--color-success, #10b981)',
            error: 'var(--color-error, #ef4444)',
            warning: 'var(--color-warning, #f59e0b)',
            info: 'var(--color-info, #3b82f6)',
        },
        fontFamily: {
            sans: typography.fontFamily.sans,
            serif: typography.fontFamily.serif,
            mono: typography.fontFamily.mono,
        },
        borderRadius: {
            ...borderRadius
        },
        extend: {
            boxShadow: {
                ...shadows
            },
            animation: {
                'fade-in': 'fadeIn 0.3s ease-in-out',
                'slide-in-right': 'slideInRight 0.3s ease-in-out',
                'slide-in-left': 'slideInLeft 0.3s ease-in-out',
                'slide-in-up': 'slideInUp 0.3s ease-in-out',
                'slide-in-down': 'slideInDown 0.3s ease-in-out',
                'bounce-in': 'bounceIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)',
                'pulse-soft': 'pulseSoft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'spin-slow': 'spin 3s linear infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: 0 },
                    '100%': { opacity: 1 },
                },
                slideInRight: {
                    '0%': { transform: 'translateX(100%)', opacity: 0 },
                    '100%': { transform: 'translateX(0)', opacity: 1 },
                },
                slideInLeft: {
                    '0%': { transform: 'translateX(-100%)', opacity: 0 },
                    '100%': { transform: 'translateX(0)', opacity: 1 },
                },
                slideInUp: {
                    '0%': { transform: 'translateY(100%)', opacity: 0 },
                    '100%': { transform: 'translateY(0)', opacity: 1 },
                },
                slideInDown: {
                    '0%': { transform: 'translateY(-100%)', opacity: 0 },
                    '100%': { transform: 'translateY(0)', opacity: 1 },
                },
                bounceIn: {
                    '0%': { transform: 'scale(0.8)', opacity: 0 },
                    '70%': { transform: 'scale(1.05)', opacity: 1 },
                    '100%': { transform: 'scale(1)', opacity: 1 },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: 1 },
                    '50%': { opacity: 0.8 },
                },
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
                'sacred-pattern': "url('/img/sacred-geometry-pattern.svg')",
            },
            backdropBlur: {
                xs: '2px',
            },
            typography: {
                DEFAULT: {
                    css: {
                        maxWidth: '65ch',
                        color: colors.neutral[800],
                        a: {
                            color: colors.primary[600],
                            '&:hover': {
                                color: colors.primary[700],
                            },
                        },
                        h1: {
                            color: colors.neutral[900],
                            fontWeight: typography.fontWeight.bold,
                        },
                        h2: {
                            color: colors.neutral[900],
                            fontWeight: typography.fontWeight.semibold,
                        },
                        h3: {
                            color: colors.neutral[900],
                            fontWeight: typography.fontWeight.semibold,
                        },
                        h4: {
                            color: colors.neutral[900],
                            fontWeight: typography.fontWeight.semibold,
                        },
                    },
                },
            },
        },
    },

    plugins: [
        forms,
        function({ addUtilities }) {
            const newUtilities = {
                '.text-shadow-sm': {
                    textShadow: '0 1px 2px rgba(0, 0, 0, 0.05)',
                },
                '.text-shadow': {
                    textShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
                },
                '.text-shadow-md': {
                    textShadow: '0 4px 8px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.08)',
                },
                '.text-shadow-lg': {
                    textShadow: '0 15px 30px rgba(0, 0, 0, 0.11), 0 5px 15px rgba(0, 0, 0, 0.08)',
                },
                '.text-shadow-none': {
                    textShadow: 'none',
                },
                '.glassmorphism': {
                    'background': 'rgba(255, 255, 255, 0.25)',
                    'backdrop-filter': 'blur(8px)',
                    '-webkit-backdrop-filter': 'blur(8px)',
                    'border': '1px solid rgba(255, 255, 255, 0.18)',
                },
                '.glassmorphism-dark': {
                    'background': 'rgba(17, 24, 39, 0.75)',
                    'backdrop-filter': 'blur(8px)',
                    '-webkit-backdrop-filter': 'blur(8px)',
                    'border': '1px solid rgba(255, 255, 255, 0.08)',
                },
            }
            addUtilities(newUtilities, ['responsive', 'hover'])
        }
    ],
};
