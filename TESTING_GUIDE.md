# End-to-End Testing Guide

## Overview
This guide provides comprehensive instructions for testing the Church Management System UI/UX improvements end-to-end.

## Prerequisites

### Local Development Environment
```bash
# Ensure you have installed:
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL/PostgreSQL database
```

### Setup Commands
```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_DATABASE=church_test
DB_USERNAME=root
DB_PASSWORD=secret

# Run migrations and seeders
php artisan migrate --seed

# Start development server
php artisan serve
```

## Testing Strategy

### 1. Automated Browser Testing (Cypress)

#### Installation
```bash
npm install cypress --save-dev
npx cypress open
```

#### Test Structure
Create `cypress/e2e/ui-improvements.cy.js`:

```javascript
describe('UI/UX Improvements E2E Tests', () => {
  
  beforeEach(() => {
    cy.loginAsAdmin(); // Custom command to login
  });

  describe('Authentication Pages', () => {
    it('should show password visibility toggle', () => {
      cy.visit('/login');
      cy.get('[data-testid="password-toggle"]').click();
      cy.get('#password').should('have.attr', 'type', 'text');
      cy.get('[data-testid="password-toggle"]').click();
      cy.get('#password').should('have.attr', 'type', 'password');
    });

    it('should validate email in real-time', () => {
      cy.visit('/register');
      cy.get('#email').type('invalid-email');
      cy.get('[data-testid="email-error"]').should('be.visible');
      cy.get('#email').clear().type('valid@example.com');
      cy.get('[data-testid="email-success"]').should('be.visible');
    });

    it('should display password strength indicator', () => {
      cy.visit('/register');
      cy.get('#password').type('weak');
      cy.get('[data-testid="password-strength"]').should('have.class', 'weak');
      cy.get('#password').clear().type('StrongP@ssw0rd123');
      cy.get('[data-testid="password-strength"]').should('have.class', 'strong');
    });
  });

  describe('Dashboard', () => {
    it('should show personalized greeting based on time', () => {
      cy.visit('/dashboard');
      const hour = new Date().getHours();
      let expectedGreeting;
      if (hour < 12) expectedGreeting = 'Good morning';
      else if (hour < 18) expectedGreeting = 'Good afternoon';
      else expectedGreeting = 'Good evening';
      
      cy.contains(expectedGreeting).should('be.visible');
    });

    it('should display quick actions panel', () => {
      cy.visit('/dashboard');
      cy.get('[data-testid="quick-actions"]').should('be.visible');
      cy.get('[data-testid="quick-action-add-member"]').should('exist');
      cy.get('[data-testid="quick-action-add-donation"]').should('exist');
      cy.get('[data-testid="quick-action-create-event"]').should('exist');
    });

    it('should make stat cards clickable', () => {
      cy.visit('/dashboard');
      cy.get('[data-testid="stat-card-members"]').click();
      cy.url().should('include', '/members');
      
      cy.visit('/dashboard');
      cy.get('[data-testid="stat-card-donations"]').click();
      cy.url().should('include', '/donations');
    });
  });

  describe('Members Management', () => {
    it('should search members with debounce', () => {
      cy.visit('/members');
      cy.get('[data-testid="search-input"]').type('John');
      cy.wait(300); // Wait for debounce
      cy.get('[data-testid="member-row"]').should('have.length.greaterThan', 0);
    });

    it('should filter by member status', () => {
      cy.visit('/members');
      cy.get('[data-testid="filter-active"]').click();
      cy.get('[data-testid="member-status-badge"]').each($badge => {
        cy.wrap($badge).should('contain.text', 'Active');
      });
    });

    it('should display member avatars with initials', () => {
      cy.visit('/members');
      cy.get('[data-testid="member-avatar"]').first().should('be.visible');
      cy.get('[data-testid="member-avatar"]').first().invoke('text')
        .should('match', /^[A-Z]{2}$/); // Two capital letters
    });

    it('should paginate member list', () => {
      cy.visit('/members');
      cy.get('[data-testid="pagination-next"]').click();
      cy.url().should('include', 'page=2');
    });
  });

  describe('Events Management', () => {
    it('should filter events by date range presets', () => {
      cy.visit('/events');
      cy.get('[data-testid="date-range-this-month"]').click();
      cy.wait('@filterEvents');
      cy.get('[data-testid="event-card"]').each($card => {
        // Verify all events are within current month
      });
    });

    it('should toggle between grid and list view', () => {
      cy.visit('/events');
      cy.get('[data-testid="view-toggle-grid"]').click();
      cy.get('[data-testid="events-grid"]').should('be.visible');
      
      cy.get('[data-testid="view-toggle-list"]').click();
      cy.get('[data-testid="events-list"]').should('be.visible');
    });

    it('should display event status badges', () => {
      cy.visit('/events');
      cy.get('[data-testid="event-status-upcoming"]').should('exist');
      cy.get('[data-testid="event-status-ongoing"]').should('exist');
      cy.get('[data-testid="event-status-completed"]').should('exist');
    });
  });

  describe('Donations', () => {
    it('should apply preset date filters', () => {
      cy.visit('/donations');
      cy.get('[data-testid="date-range-today"]').click();
      cy.get('[data-testid="donation-row"]').each($row => {
        // Verify all donations are from today
      });
    });

    it('should display donation statistics', () => {
      cy.visit('/donations');
      cy.get('[data-testid="stat-total-count"]').should('be.visible');
      cy.get('[data-testid="stat-total-amount"]').should('be.visible');
      cy.get('[data-testid="stat-average-amount"]').should('be.visible');
    });

    it('should sort donations by column', () => {
      cy.visit('/donations');
      cy.get('[data-testid="sort-amount"]').click();
      cy.get('[data-testid="donation-row"]').first()
        .invoke('text')
        .then(firstAmount => {
          cy.get('[data-testid="sort-amount"]').click(); // Reverse sort
          cy.get('[data-testid="donation-row"]').first()
            .invoke('text')
            .should('not.equal', firstAmount);
        });
    });
  });

  describe('Donation Creation Form', () => {
    it('should select donation category', () => {
      cy.visit('/donations/create');
      cy.get('[data-testid="category-select"]').select('Tithe');
      cy.get('[data-testid="category-select"]').should('have.value', 'tithe');
    });

    it('should select payment method', () => {
      cy.visit('/donations/create');
      cy.get('[data-testid="payment-method-cash"]').click();
      cy.get('[data-testid="payment-method-cash"]').should('have.class', 'selected');
    });

    it('should use amount presets', () => {
      cy.visit('/donations/create');
      cy.get('[data-testid="amount-preset-50"]').click();
      cy.get('#amount').should('have.value', '50.00');
    });

    it('should search member with autocomplete', () => {
      cy.visit('/donations/create');
      cy.get('[data-testid="member-search"]').type('John');
      cy.wait(300);
      cy.get('[data-testid="member-suggestion"]').first().click();
      cy.get('[data-testid="selected-member"]').should('be.visible');
    });
  });

  describe('Admin Users Management', () => {
    it('should filter users by role', () => {
      cy.visit('/admin/users');
      cy.get('[data-testid="role-filter-admin"]').click();
      cy.get('[data-testid="user-role-badge"]').each($badge => {
        cy.wrap($badge).should('contain.text', 'Admin');
      });
    });

    it('should display user avatars with initials', () => {
      cy.visit('/admin/users');
      cy.get('[data-testid="user-avatar"]').first().should('be.visible');
    });

    it('should show last login information', () => {
      cy.visit('/admin/users');
      cy.get('[data-testid="last-login"]').first().should('be.visible');
    });

    it('should toggle user status', () => {
      cy.visit('/admin/users');
      cy.get('[data-testid="status-toggle"]').first().click();
      cy.wait('@updateStatus');
      cy.get('[data-testid="status-badge"]').first()
        .should('have.class', 'active');
    });

    it('should perform bulk actions', () => {
      cy.visit('/admin/users');
      cy.get('[data-testid="bulk-select"]').first().check();
      cy.get('[data-testid="bulk-select"]').eq(1).check();
      cy.get('[data-testid="bulk-delete"]').click();
      cy.get('[data-testid="confirm-delete"]').click();
    });
  });

  describe('Reports Management', () => {
    it('should filter reports by type', () => {
      cy.visit('/reports');
      cy.get('[data-testid="report-type-filter"]').select('Financial');
      cy.get('[data-testid="report-row"]').each($row => {
        // Verify all reports are financial type
      });
    });

    it('should display report icons by category', () => {
      cy.visit('/reports');
      cy.get('[data-testid="report-icon-financial"]').should('exist');
      cy.get('[data-testid="report-icon-membership"]').should('exist');
    });

    it('should export report', () => {
      cy.visit('/reports');
      cy.get('[data-testid="export-pdf"]').first().click();
      // Verify PDF download initiated
    });
  });

  describe('Accessibility', () => {
    it('should have proper ARIA labels', () => {
      cy.visit('/dashboard');
      cy.get('[aria-label="Dashboard"]').should('exist');
      cy.get('[aria-label="Quick actions"]').should('exist');
    });

    it('should support keyboard navigation', () => {
      cy.visit('/login');
      cy.tab().tab().tab(); // Navigate through inputs
      cy.focused().should('have.attr', 'type', 'password');
    });

    it('should maintain focus management in modals', () => {
      cy.visit('/members');
      cy.get('[data-testid="add-member-btn"]').click();
      cy.focused().should('have.attr', 'id', 'member-name');
      cy.get('[data-testid="close-modal"]').click();
      cy.focused().should('have.attr', 'data-testid', 'add-member-btn');
    });

    it('should meet color contrast requirements', () => {
      cy.visit('/login');
      cy.get('body').should('have.css', 'color')
        .and((color) => {
          // Verify contrast ratio meets WCAG AA standards
        });
    });
  });

  describe('Responsive Design', () => {
    it('should adapt to mobile viewport', () => {
      cy.viewport('iphone-13');
      cy.visit('/dashboard');
      cy.get('[data-testid="mobile-menu"]').should('be.visible');
      cy.get('[data-testid="desktop-stats"]').should('not.be.visible');
    });

    it('should adapt to tablet viewport', () => {
      cy.viewport('ipad-2');
      cy.visit('/members');
      cy.get('[data-testid="tablet-layout"]').should('exist');
    });
  });

  describe('Performance', () => {
    it('should load dashboard within 2 seconds', () => {
      cy.visit('/dashboard', { timeout: 2000 });
      cy.get('[data-testid="dashboard-loaded"]').should('be.visible');
    });

    it('should debounce search input', () => {
      cy.visit('/members');
      cy.get('[data-testid="search-input"]').type('Test');
      cy.wait(250);
      cy.get('@searchRequest').its('request.url')
        .should('include', 'search=Test');
    });
  });
});
```

#### Custom Commands
Create `cypress/support/commands.js`:

```javascript
Cypress.Commands.add('loginAsAdmin', () => {
  cy.session('admin', () => {
    cy.visit('/login');
    cy.get('#email').type('admin@church.com');
    cy.get('#password').type('password');
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/dashboard');
  });
});

Cypress.Commands.add('loginAsUser', (email, password) => {
  cy.session(email, () => {
    cy.visit('/login');
    cy.get('#email').type(email);
    cy.get('#password').type(password);
    cy.get('button[type="submit"]').click();
    cy.url().should('include', '/dashboard');
  });
});

Cypress.Commands.add('waitForDebounce', (ms = 300) => {
  cy.wait(ms);
});
```

### 2. Manual Testing Checklist

#### Authentication Pages
- [ ] Password visibility toggle works on both login and register
- [ ] Email validation shows error for invalid format
- [ ] Email validation shows success for valid format
- [ ] Password strength indicator updates in real-time
- [ ] Password match validation works on confirm field
- [ ] Form submission prevented when validation fails
- [ ] Error messages are clear and helpful
- [ ] Success messages display after registration
- [ ] Keyboard navigation works through all fields
- [ ] Screen reader announces field states correctly

#### Dashboard
- [ ] Greeting changes based on time of day
- [ ] Quick actions panel is visible and functional
- [ ] All quick action buttons navigate correctly
- [ ] Stat cards are clickable and navigate to correct pages
- [ ] Stats display accurate data
- [ ] "View all" links work on each module card
- [ ] Layout adapts to mobile screens
- [ ] No horizontal scrolling on any viewport
- [ ] Loading states display while fetching data

#### Members Management
- [ ] Search filters members in real-time
- [ ] Search debounces properly (no excessive requests)
- [ ] Status filter shows only selected status
- [ ] Sorting works on all columns
- [ ] Pagination navigates correctly
- [ ] Member avatars display initials correctly
- [ ] Status badges show correct colors
- [ ] Empty state displays when no members match
- [ ] Add member button navigates to form
- [ ] Edit button opens edit modal/page
- [ ] Delete confirmation appears before deletion
- [ ] Responsive table on mobile (horizontal scroll or stacked)

#### Events Management
- [ ] Date range presets filter correctly
- [ ] Custom date range picker works
- [ ] Sort options change order correctly
- [ ] Grid view displays cards properly
- [ ] List view displays rows properly
- [ ] View toggle persists during session
- [ ] Event status badges show correct colors
- [ ] Event cards show all necessary information
- [ ] Create event button works
- [ ] Edit event button works
- [ ] Delete event confirmation appears
- [ ] Empty state displays appropriately

#### Donations Page
- [ ] Date range presets filter correctly
- [ ] Statistics cards show accurate calculations
- [ ] Table sorting works on all columns
- [ ] Pagination works correctly
- [ ] Search filters by member name/amount
- [ ] Clear filters resets all filters
- [ ] Add donation button navigates to form
- [ ] Edit donation button works
- [ ] Send receipt button triggers email
- [ ] Delete confirmation appears
- [ ] Empty state with illustration displays

#### Donation Creation Form
- [ ] Category dropdown populates correctly
- [ ] Payment method selection highlights chosen option
- [ ] Amount presets fill input field
- [ ] Custom amount can be entered
- [ ] Date picker allows date selection
- [ ] Member search autocompletes
- [ ] Selected member displays clearly
- [ ] Notes field accepts multi-line text
- [ ] Form validation prevents invalid submission
- [ ] Success message displays after creation
- [ ] Cancel button returns to donations list

#### Admin Users Management
- [ ] Search filters users correctly
- [ ] Role filter shows only selected role
- [ ] Status filter works correctly
- [ ] User avatars display initials
- [ ] Last login shows relative time
- [ ] Status toggle updates immediately
- [ ] Bulk selection checkboxes work
- [ ] Bulk delete confirms and deletes
- [ ] Create user form validates properly
- [ ] Password visibility toggle works
- [ ] Role assignment works in create/edit
- [ ] Edit user pre-fills form correctly

#### Reports Management
- [ ] Report type filter works
- [ ] Date range presets filter correctly
- [ ] Search filters by report name/description
- [ ] Report icons display by category
- [ ] Description preview truncates long text
- [ ] Type badges show correct colors
- [ ] Timestamp displays relative time
- [ ] Export buttons initiate downloads
- [ ] Clear filters resets all
- [ ] Create report form validates

#### Accessibility
- [ ] All interactive elements have focus indicators
- [ ] Tab order is logical through pages
- [ ] Skip to main content link works
- [ ] ARIA labels present on icon buttons
- [ ] Form fields have associated labels
- [ ] Error messages linked to inputs via aria-describedby
- [ ] Color contrast meets WCAG AA (4.5:1)
- [ ] Screen reader announces page changes
- [ ] Modal traps focus correctly
- [ ] Keyboard can close modals with Escape
- [ ] No content relies solely on color

#### Performance
- [ ] Initial page load < 2 seconds
- [ ] Search results appear within 300ms after typing stops
- [ ] No layout shift during loading
- [ ] Images optimized and lazy loaded
- [ ] No console errors in browser dev tools
- [ ] Memory usage stable during extended use

#### Cross-Browser Testing
- [ ] Chrome (latest) - All features work
- [ ] Firefox (latest) - All features work
- [ ] Safari (latest) - All features work
- [ ] Edge (latest) - All features work
- [ ] Mobile Safari (iOS) - Responsive and functional
- [ ] Chrome Mobile (Android) - Responsive and functional

### 3. Automated Accessibility Testing (axe-core)

#### Installation
```bash
npm install axe-core @axe-core/cypress --save-dev
```

#### Configuration
Add to `cypress/support/e2e.js`:
```javascript
import 'cypress-axe';
```

#### Example Test
```javascript
it('should not have accessibility violations', () => {
  cy.visit('/dashboard');
  cy.injectAxe();
  cy.checkA11y(null, {
    rules: {
      'color-contrast': { enabled: true },
      'label': { enabled: true },
      'button-name': { enabled: true }
    }
  });
});
```

### 4. Visual Regression Testing (Percy/Lookbook)

#### Percy Setup
```bash
npm install @percy/cli @percy/cypress --save-dev
```

#### Example Test
```javascript
it('should look correct on dashboard', () => {
  cy.visit('/dashboard');
  cy.percySnapshot('Dashboard - Desktop');
  
  cy.viewport('iphone-13');
  cy.percySnapshot('Dashboard - Mobile');
});
```

### 5. Performance Testing (Lighthouse)

#### Automated Lighthouse CI
```bash
npm install -g @lhci/cli
lhci autorun
```

#### Configuration (.lighthouserc.json)
```json
{
  "ci": {
    "collect": {
      "staticDistDir": "./public",
      "url": [
        "http://localhost:8000/login",
        "http://localhost:8000/dashboard",
        "http://localhost:8000/members"
      ]
    },
    "assert": {
      "preset": "lighthouse:recommended",
      "assertions": {
        "categories:performance": ["error", {"minScore": 0.9}],
        "categories:accessibility": ["error", {"minScore": 0.95}]
      }
    }
  }
}
```

## Running Tests

### Full Test Suite
```bash
# Cypress headless mode
npx cypress run

# With specific browser
npx cypress run --browser chrome

# With recording to Cypress Cloud
npx cypress run --record --key your-key
```

### Specific Test Files
```bash
npx cypress run --spec "cypress/e2e/ui-improvements.cy.js"
```

### Interactive Mode
```bash
npx cypress open
```

### Parallel Testing
```bash
npx cypress run --parallel --group "UI Tests"
```

## Continuous Integration

### GitHub Actions Workflow
Create `.github/workflows/e2e-tests.yml`:

```yaml
name: E2E Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  e2e-tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: church_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          
      - name: Install dependencies
        run: |
          composer install --no-interaction
          npm ci
          
      - name: Setup environment
        run: |
          cp .env.example .env
          php artisan key:generate
          
      - name: Run migrations
        run: php artisan migrate --force
        
      - name: Seed database
        run: php artisan db:seed
        
      - name: Start server
        run: php artisan serve --host=127.0.0.1 --port=8000 &
        
      - name: Wait for server
        run: sleep 10
        
      - name: Run Cypress tests
        uses: cypress-io/github-action@v5
        with:
          start: npm run cypress:open
          wait-on: 'http://127.0.0.1:8000'
          record: true
        env:
          CYPRESS_RECORD_KEY: ${{ secrets.CYPRESS_RECORD_KEY }}
```

## Test Data Management

### Factory Seeding
Create test data factory in `database/factories/UserFactory.php`:

```php
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
        'role' => 'admin',
        'created_at' => now(),
    ];
}
```

### Cypress Fixtures
Create `cypress/fixtures/users.json`:
```json
[
  {
    "email": "admin@church.com",
    "password": "password",
    "role": "admin"
  },
  {
    "email": "member@church.com",
    "password": "password",
    "role": "member"
  }
]
```

## Reporting

### HTML Reports
```bash
npm install mochawesome --save-dev
npx cypress run --reporter mochawesome
```

### JUnit Reports for CI
```bash
npx cypress run --reporter junit --reporter-options mochaFile=results/junit.xml
```

## Troubleshooting

### Common Issues

**Tests fail due to timing:**
```javascript
// Use explicit waits instead of fixed delays
cy.get('[data-testid="element"]').should('be.visible');
cy.wait('@apiCall'); // Wait for network request
```

**Flaky tests:**
```javascript
// Retry failed tests
{
  "retries": {
    "runMode": 2,
    "openMode": 0
  }
}
```

**Database state issues:**
```javascript
// Reset database before each test
beforeEach(() => {
  cy.task('db:seed'); // Custom task to reset DB
});
```

## Success Criteria

✅ **All automated tests pass** (95%+ pass rate)  
✅ **No critical accessibility violations** (WCAG AA compliant)  
✅ **Performance scores above 90** (Lighthouse)  
✅ **Cross-browser compatibility verified**  
✅ **Mobile responsiveness confirmed**  
✅ **Manual testing checklist completed**  

## Next Steps After Testing

1. Fix any failing tests
2. Address accessibility violations
3. Optimize performance bottlenecks
4. Document known issues
5. Schedule regular regression testing
6. Integrate into CI/CD pipeline
7. Set up monitoring for production
