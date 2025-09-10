![Church Management System Logo](public/images/logo.png)

# Church Management System

A comprehensive church management system built with Laravel, designed to help churches manage members, groups, events, and more.

[![Build Status](https://github.com/yourusername/church-management/actions/workflows/tests.yml/badge.svg)](https://github.com/yourusername/church-management/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/framework)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net/)

## Features

### Core Functionality
- **Member Management**: Track member information, attendance, and involvement
- **Groups & Ministries**: Organize members into groups and track ministry participation
- **Event Management**: Schedule and manage church events and services with a full-featured calendar
- **Attendance Tracking**: Record and analyze attendance for services and events
- **Donation Tracking**: Record and manage tithes and offerings
- **Reporting**: Generate reports on attendance, giving, and member engagement
- **API Access**: RESTful API for integration with other systems
- **Role-based Access Control**: Secure access control with customizable permissions

### Modern UI/UX Features
- **Responsive Design**: Optimized for all devices from mobile to desktop
- **Dark Mode**: Full dark mode support throughout the application
- **Theme Customization**: Personalize the application with custom colors and presets
- **Seasonal Themes**: Automatic theme changes based on church calendar seasons
- **Interactive Calendar**: Drag-and-drop event scheduling with multiple view options
- **Accessibility Features**: High contrast mode, font size adjustments, and reduced animations

### UI Component Library
- **Button Component**: Various variants, sizes, and states
- **Toast Notifications**: Different types and positions for user feedback
- **Modal Component**: Form integration and confirmation dialogs
- **Dropdown Menus**: Custom triggers and styling
- **Tab Component**: Different styles and content switching
- **Table Component**: Sorting, pagination, and custom cell rendering
- **Form Components**: Validation and different input types

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL 5.7+ or MariaDB 10.3+
- Node.js & NPM (for frontend assets)
- Web server (Apache/Nginx)

## Calendar and Event Management System

The application includes a comprehensive calendar and event management system that allows church administrators to schedule, manage, and share church events with members.

### Features
- **Interactive Calendar**: Full-featured calendar with month, week, day, and list views
- **Event Categories**: Organize events by category with color coding
- **Recurring Events**: Set up recurring events with customizable patterns
- **Resource Management**: Track resources needed for events
- **Reminders**: Set up email and notification reminders for events
- **Filtering**: Filter events by category, search terms, or date range
- **Dark Mode Support**: Calendar fully supports the application's dark mode

## Attendance Tracking System

The application includes a robust attendance tracking system that allows church administrators to record, analyze, and report on attendance for services and events.

### Features
- **Check-in System**: Multiple check-in methods including manual entry, QR code scanning, and kiosk mode
- **Real-time Tracking**: Monitor attendance during services with active check-in sessions
- **Guest Registration**: Record and categorize guest attendance
- **Attendance Reports**: View trends and patterns in attendance data
- **Member Attendance History**: Track individual member attendance over time
- **Attendance Categories**: Customize categories for different demographic groups
- **Export Capabilities**: Export attendance data for further analysis
- **Attendance Settings**: Configure check-in methods, reminders, and default categories

## Theme Customization System

The application includes a comprehensive theme customization system that allows administrators to personalize the look and feel of the church management system.

### Features

- **Theme Presets**: Choose from multiple predefined themes including Default, Modern, Classic, Vibrant, and Minimal
- **Custom Colors**: Customize primary, secondary, and accent colors to match your church's branding
- **Seasonal Themes**: Automatic theme changes for church calendar seasons (Advent, Christmas, Lent, Easter, Pentecost, etc.)
- **Dark Mode**: System-wide dark mode that respects user preferences and can be toggled manually
- **Accessibility Options**:
  - High contrast mode for users with visual impairments
  - Font size adjustments (small, medium, large)
  - Reduced animations for users sensitive to motion

### How to Use

1. Navigate to **Settings > Theme Customization** in the main navigation
2. Select a theme preset or customize your own colors
3. Enable/disable seasonal themes based on your preferences
4. Configure accessibility options as needed
5. Apply changes to see them immediately throughout the application

### Technical Implementation

- Built with CSS variables for dynamic theming without page reloads
- Leverages Tailwind CSS for consistent styling and dark mode support
- Uses Pinia store for persistent settings across sessions
- Implements Vue.js components for interactive theme customization

## UI Component Demo

The application includes a comprehensive UI Component Demo page that showcases all available UI components and serves as both documentation and a testing ground for developers.

### Features

- **Interactive Examples**: Live demonstrations of all UI components with code snippets
- **Variant Showcase**: Display of different states, sizes, and variants for each component
- **Accessibility Testing**: Verify that all components work with keyboard navigation and screen readers
- **Dark Mode Preview**: Test components in both light and dark modes

### How to Access

1. Log in to the application
2. Navigate to **Settings > UI Component Demo** in the main navigation
3. Browse through the different component sections

### Available Components

- **Buttons**: Primary, secondary, outline, text, with icons, loading states
- **Forms**: Input fields, checkboxes, radio buttons, select menus, date pickers
- **Feedback**: Toast notifications, alerts, progress indicators
- **Navigation**: Tabs, breadcrumbs, pagination
- **Data Display**: Tables, cards, badges, chips
- **Overlays**: Modals, dialogs, popovers, tooltips
- **Layout**: Containers, grids, dividers

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/church-management.git
   cd church-management
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   npm run dev
   ```

4. **Create environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Optional branding keys you can set in `.env`:
   ```
   ORG_NAME="Your Church Name"
   ORG_ADDRESS="123 Main Street, City, State, ZIP"
   ORG_PHONE="(555) 123-4567"
   ORG_EMAIL="info@yourchurch.org"
   ORG_WEBSITE="https://yourchurch.org"
   ORG_TAX_ID="12-3456789"
   ORG_LOGO="images/logo.png"
   ```

5. **Configure database**
   Update the `.env` file with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=church_management
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```

6. **Run migrations and seed the database**
   ```bash
   php artisan migrate --seed
   ```

7. **Create storage link**
   ```bash
   php artisan storage:link
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

9. **Access the application**
   Open your browser and visit: `http://localhost:8000`

## Default Login Credentials

- **Email**: admin@example.com
- **Password**: password

## API Documentation

API documentation is available using Postman. Import the collection from:
`postman/Church Management API.postman_collection.json`

### API Authentication

1. Login to get an access token:
   ```
   POST /api/auth/login
   {
       "email": "admin@example.com",
       "password": "password"
   }
   ```

2. Use the returned token in subsequent requests:
   ```
   Authorization: Bearer your_token_here
   ```

## Testing

Run the tests with:

```bash
php artisan test
```

## State Management

The application has been migrated from Vuex to Pinia for state management, providing better TypeScript support, dev tools integration, and a more intuitive API.

### Key Stores

- **Auth Store**: Manages user authentication, login/logout processes, and user data
- **Settings Store**: Handles application settings including dark mode, theme customization, and user preferences

### Benefits of Pinia

- **Modular by design**: Stores are independent and can be imported only where needed
- **TypeScript support**: Full type safety for store properties and actions
- **Developer experience**: Better debugging with Vue DevTools integration
- **Composition API compatibility**: Seamless integration with Vue 3's Composition API
- **Simpler syntax**: More intuitive API compared to Vuex

### Implementation

```javascript
// Example of using the settings store with Composition API
import { useSettingsStore } from '@/stores/settings';
import { storeToRefs } from 'pinia';

// In your component's setup
const settingsStore = useSettingsStore();
const { darkMode, themePreset } = storeToRefs(settingsStore);

// Call actions
settingsStore.toggleDarkMode();
settingsStore.setThemePreset('modern');
```

## Contributing

Contributions are welcome! Please read our [contributing guidelines](CONTRIBUTING.md) before submitting pull requests.

## Security Vulnerabilities

If you discover a security vulnerability, please send an email to security@example.com. All security vulnerabilities will be promptly addressed.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
