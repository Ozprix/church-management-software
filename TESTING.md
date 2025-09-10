# Church Management System Testing Documentation

This document outlines the testing strategy and procedures for the Church Management System.

## Testing Strategy

Our testing approach follows a comprehensive multi-level strategy:

### 1. Frontend Testing (Vue Components)

- **Unit Tests**: Test individual Vue components in isolation
- **Integration Tests**: Test component interactions
- **Store Tests**: Test Pinia store functionality
- **End-to-End Tests**: Test complete user flows

### 2. Backend Testing (Laravel)

- **Unit Tests**: Test individual classes and methods
- **Feature Tests**: Test API endpoints and controllers
- **Database Tests**: Test database interactions

## Test Coverage

### Frontend Components

- **Onboarding Components**
  - `FeatureTour.vue`: Step-by-step guidance for specific features
  - `OnboardingSettings.vue`: User preferences for onboarding experience
  - `ContextualHelp.vue`: Context-aware help system
  - `PledgeCampaignTour.vue`: Specialized tour for pledge campaign management

- **Donation Components**
  - `DonationInsights.vue`: Data visualization for donation patterns
  - `PledgeFulfillmentReport.vue`: Reporting on pledge campaign progress

- **Stores**
  - `onboarding.js`: State management for onboarding features

### Backend Features

- **Authentication**: Login, registration, password reset
- **Member Management**: CRUD operations, import/export
- **Donation Management**: Recording, tracking, reporting
- **API Endpoints**: RESTful API functionality

## Running Tests

### Prerequisites

- PHP 8.1+
- Node.js 16+
- Composer
- NPM

### Running All Tests

Use the comprehensive test script to run all tests:

```powershell
.\run-all-tests.ps1
```

This script will:
1. Run PHP Unit Tests (Backend)
2. Run JavaScript Unit Tests (Frontend)
3. Check PHP Code Style
4. Check JavaScript Code Style
5. Run Security Checks

### Running Specific Tests

#### Frontend Tests

```bash
# Run all JavaScript tests
npm test

# Run tests in watch mode (for development)
npm run test:watch

# Run tests with coverage report
npm run test:coverage

# Run a specific test file
npx jest tests/js/components/onboarding/FeatureTour.spec.js
```

#### Backend Tests

```bash
# Run all PHP tests
php artisan test

# Run a specific test file
php artisan test tests/Feature/DashboardAccessTest.php

# Run tests with coverage report
php artisan test --coverage
```

## Test Files Structure

```
tests/
├── Feature/                # Laravel feature tests
│   ├── Api/                # API endpoint tests
│   ├── Auth/               # Authentication tests
│   └── ...
├── Unit/                   # Laravel unit tests
│   ├── Models/             # Model tests
│   ├── Services/           # Service tests
│   └── ...
└── js/                     # JavaScript tests
    ├── components/         # Vue component tests
    │   ├── onboarding/     # Onboarding component tests
    │   ├── donations/      # Donation component tests
    │   └── reports/        # Report component tests
    └── stores/             # Pinia store tests
```

## Continuous Integration

The test suite is configured to run automatically in our CI/CD pipeline on:
- Every push to the main branch
- Every pull request
- Scheduled nightly builds

## Writing New Tests

### Frontend Component Tests

1. Create a new test file in the appropriate directory under `tests/js/components/`
2. Import the component and any necessary dependencies
3. Mock external dependencies (stores, services, etc.)
4. Write test cases that cover component functionality
5. Run tests to verify

Example:
```javascript
import { mount } from '@vue/test-utils';
import MyComponent from '../../../../resources/js/components/MyComponent.vue';

describe('MyComponent.vue', () => {
  let wrapper;

  beforeEach(() => {
    wrapper = mount(MyComponent);
  });

  test('renders correctly', () => {
    expect(wrapper.find('.my-component').exists()).toBe(true);
  });
});
```

### Backend Tests

1. Create a new test file in the appropriate directory under `tests/Feature/` or `tests/Unit/`
2. Use Laravel's testing utilities to test functionality
3. Run tests to verify

Example:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_example()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->get('/dashboard');
        
        $response->assertStatus(200);
    }
}
```

## Best Practices

1. **Test Isolation**: Each test should be independent and not rely on the state from other tests
2. **Mock External Dependencies**: Use mocks for API calls, stores, and services
3. **Test Edge Cases**: Include tests for error conditions and edge cases
4. **Keep Tests Fast**: Tests should run quickly to encourage frequent testing
5. **Test Meaningful Behavior**: Focus on testing behavior, not implementation details
6. **Maintain Test Coverage**: Aim for high test coverage, especially for critical functionality
7. **Update Tests with Code Changes**: Keep tests in sync with code changes

## Troubleshooting

### Common Issues

1. **Tests Failing Due to Database Issues**
   - Ensure your test database is configured correctly in `phpunit.xml`
   - Use the `RefreshDatabase` trait to reset the database between tests

2. **JavaScript Tests Not Finding Components**
   - Check import paths in test files
   - Verify Jest configuration in `jest.config.js`

3. **Mock Issues**
   - Ensure mocks are properly set up before tests run
   - Clear mock state between tests with `jest.clearAllMocks()`

For additional help, contact the development team or refer to the testing framework documentation:
- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [Vue Test Utils Documentation](https://test-utils.vuejs.org/)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
