# End-to-End Testing Guide

## Overview
This guide explains how to run end-to-end (E2E) tests for the Church Management System's UI/UX improvements.

## Test Environment Setup

### Prerequisites
Since PHP is not available in this environment, we've created comprehensive **manual testing checklists** and **automated test specifications** that can be run when the application is deployed.

## Automated Test Suites

### 1. Cypress E2E Tests
Location: `/workspace/cypress/e2e/`

**Existing Tests:**
- `contextual-help.spec.js` - Contextual help system
- `church-management/donations.cy.js` - Donation workflows
- `church-management/onboarding.cy.js` - Onboarding flows
- `church-management/pledge-fulfillment.cy.js` - Pledge campaigns

**New Test Coverage Needed for UI/UX Improvements:**

#### Authentication Tests
```javascript
// cypress/e2e/auth/login.cy.js
describe('Authentication UI/UX', () => {
  it('should show/hide password when toggle clicked', () => {
    cy.visit('/login');
    cy.get('[data-testid="password-toggle"]').click();
    cy.get('input[name="password"]').should('have.attr', 'type', 'text');
  });

  it('should validate email in real-time', () => {
    cy.visit('/login');
    cy.get('input[name="email"]').type('invalid-email');
    cy.get('[data-testid="email-error"]').should('be.visible');
  });

  it('should display password strength indicator', () => {
    cy.visit('/register');
    cy.get('input[name="password"]').type('weak');
    cy.get('[data-testid="password-strength"]').should('contain', 'Weak');
  });
});
```

#### Dashboard Tests
```javascript
// cypress/e2e/dashboard/dashboard.cy.js
describe('Dashboard UI/UX', () => {
  it('should display personalized greeting based on time', () => {
    cy.login();
    cy.visit('/dashboard');
    cy.get('[data-testid="greeting"]').should('match', /Good (morning|afternoon|evening)/);
  });

  it('should show quick actions panel', () => {
    cy.login();
    cy.visit('/dashboard');
    cy.get('[data-testid="quick-actions"]').should('be.visible');
    cy.get('[data-testid="quick-action-add-member"]').should('exist');
    cy.get('[data-testid="quick-action-record-donation"]').should('exist');
  });

  it('should make stat cards clickable', () => {
    cy.login();
    cy.visit('/dashboard');
    cy.get('[data-testid="stat-card-members"]').click();
    cy.url().should('include', '/members');
  });
});
```

#### Members Management Tests
```javascript
// cypress/e2e/members/members.cy.js
describe('Members Management UI/UX', () => {
  it('should search members with debounce', () => {
    cy.login();
    cy.visit('/members');
    cy.get('[data-testid="search-input"]').type('John');
    cy.get('[data-testid="member-list"]').should('contain', 'John');
  });

  it('should filter by status', () => {
    cy.login();
    cy.visit('/members');
    cy.get('[data-testid="filter-active"]').click();
    cy.get('[data-testid="member-row"]').each(($row) => {
      cy.wrap($row).should('contain', 'Active');
    });
  });

  it('should display member avatars with initials', () => {
    cy.login();
    cy.visit('/members');
    cy.get('[data-testid="member-avatar"]').should('exist');
    cy.get('[data-testid="member-avatar"]').first().should('not.be.empty');
  });
});
```

#### Events Management Tests
```javascript
// cypress/e2e/events/events.cy.js
describe('Events Management UI/UX', () => {
  it('should toggle between grid and list view', () => {
    cy.login();
    cy.visit('/events');
    cy.get('[data-testid="view-toggle-grid"]').click();
    cy.get('[data-testid="event-card"]').should('have.length.greaterThan', 0);
    cy.get('[data-testid="view-toggle-list"]').click();
    cy.get('[data-testid="event-row"]').should('have.length.greaterThan', 0);
  });

  it('should apply date range presets', () => {
    cy.login();
    cy.visit('/events');
    cy.get('[data-testid="date-range-this-week"]').click();
    cy.get('[data-testid="event-date"]').each(($date) => {
      // Verify dates are within this week
    });
  });
});
```

#### Donations Tests
```javascript
// cypress/e2e/donations/donations-enhanced.cy.js
describe('Donations UI/UX Improvements', () => {
  it('should apply preset date ranges', () => {
    cy.login();
    cy.visit('/donations');
    cy.get('[data-testid="date-range-today"]').click();
    cy.get('[data-testid="donation-table"]').should('exist');
  });

  it('should display donation summary statistics', () => {
    cy.login();
    cy.visit('/donations');
    cy.get('[data-testid="stat-total-donations"]').should('exist');
    cy.get('[data-testid="stat-total-amount"]').should('exist');
    cy.get('[data-testid="stat-average-amount"]').should('exist');
  });

  it('should sort table columns', () => {
    cy.login();
    cy.visit('/donations');
    cy.get('[data-testid="sort-amount"]').click();
    cy.get('[data-testid="sort-amount"]').should('have.class', 'sorted');
  });
});
```

#### Admin Users Tests
```javascript
// cypress/e2e/admin/users.cy.js
describe('Admin Users Management UI/UX', () => {
  it('should display user avatars with initials', () => {
    cy.loginAsAdmin();
    cy.visit('/admin/users');
    cy.get('[data-testid="user-avatar"]').should('exist');
  });

  it('should show last login information', () => {
    cy.loginAsAdmin();
    cy.visit('/admin/users');
    cy.get('[data-testid="last-login"]').should('exist');
  });

  it('should filter users by role', () => {
    cy.loginAsAdmin();
    cy.visit('/admin/users');
    cy.get('[data-testid="role-filter-admin"]').click();
    cy.get('[data-testid="user-row"]').each(($row) => {
      cy.wrap($row).should('contain', 'Admin');
    });
  });
});
```

### 2. Running Cypress Tests

```bash
# Install dependencies
npm install

# Open Cypress interactive mode
npx cypress open

# Run all E2E tests headlessly
npx cypress run

# Run specific test file
npx cypress run --spec "cypress/e2e/auth/login.cy.js"

# Run tests in specific browser
npx cypress run --browser chrome
```

## Manual Testing Checklist

### Authentication Pages
- [ ] Password visibility toggle works on login page
- [ ] Password visibility toggle works on register page
- [ ] Real-time email validation shows errors immediately
- [ ] Password strength indicator updates as typing
- [ ] Password match validation works on confirm field
- [ ] Form submissions show proper loading states
- [ ] Error messages are clear and helpful
- [ ] Mobile responsive layout works correctly
- [ ] Keyboard navigation works (Tab through fields)
- [ ] Screen reader announces form errors

### Dashboard
- [ ] Greeting changes based on time of day
- [ ] Quick actions panel is visible and functional
- [ ] All quick action buttons navigate correctly
- [ ] Stat cards are clickable and navigate to correct pages
- [ ] "View all" links work on each module card
- [ ] Layout is responsive on mobile/tablet
- [ ] Loading states show during data fetch

### Members Management
- [ ] Search filters members in real-time
- [ ] Status filter (All/Active/Inactive) works
- [ ] Sort options change order correctly
- [ ] Member avatars display initials
- [ ] Status badges show correct colors
- [ ] Pagination works for large lists
- [ ] Empty state shows when no members found
- [ ] Action buttons (Edit/Delete) work
- [ ] Export button triggers download

### Events Management
- [ ] Search filters events correctly
- [ ] Date range presets apply filters
- [ ] Sort options work (Date, Name, Status)
- [ ] Grid/List view toggle changes layout
- [ ] Event cards show correct status badges
- [ ] Event cards display date/time correctly
- [ ] Add Event button opens form
- [ ] Edit/Delete actions work
- [ ] Responsive layout adapts to screen size

### Donations Page
- [ ] Preset date ranges filter donations
- [ ] Summary statistics display correctly
- [ ] Table columns are sortable
- [ ] Custom date range picker works
- [ ] Clear filters resets all filters
- [ ] Add Donation button opens form
- [ ] Edit/Delete actions work
- [ ] Empty state shows appropriately
- [ ] Success messages appear after actions

### Donation Creation Form
- [ ] Category dropdown populates correctly
- [ ] Payment method options are selectable
- [ ] Amount presets quickly set values
- [ ] Date picker allows date selection
- [ ] Member search autocomplete works
- [ ] Form validation shows clear errors
- [ ] Submit button shows loading state
- [ ] Success message appears on completion
- [ ] Cancel button returns to list

### Admin Users Management
- [ ] User list displays avatars with initials
- [ ] Last login timestamps show correctly
- [ ] Role filter works (All/Admin/Staff/Volunteer)
- [ ] Status filter works (All/Active/Inactive)
- [ ] Search filters by name/email
- [ ] Status badges show correct colors
- [ ] Edit user opens form with populated data
- [ ] Delete user shows confirmation dialog
- [ ] Create user form validates properly
- [ ] Password strength indicator works
- [ ] Bulk actions checkbox selects all

### Reports Management
- [ ] Report type icons display correctly
- [ ] Description previews truncate properly
- [ ] Type badges show correct colors
- [ ] Timestamp formats are readable
- [ ] Filter bar accepts date ranges
- [ ] Clear filters resets all inputs
- [ ] Generate report creates new entry
- [ ] Edit report opens populated form
- [ ] Delete report shows confirmation

### Accessibility (All Pages)
- [ ] All interactive elements have focus states
- [ ] Keyboard navigation works throughout
- [ ] ARIA labels present on icon buttons
- [ ] Color contrast meets WCAG AA standards
- [ ] Form inputs have associated labels
- [ ] Error messages announced to screen readers
- [ ] Skip links allow bypassing navigation
- [ ] Page titles are descriptive

### Mobile Responsiveness (All Pages)
- [ ] Layout adapts to 320px width (mobile)
- [ ] Layout adapts to 768px width (tablet)
- [ ] Touch targets are at least 44x44px
- [ ] No horizontal scrolling required
- [ ] Text remains readable without zooming
- [ ] Navigation collapses to hamburger menu
- [ ] Tables become scrollable or stack

## Performance Testing

### Lighthouse Scores Target
Run Chrome DevTools > Lighthouse for each page:

- **Performance**: ≥90
- **Accessibility**: ≥95
- **Best Practices**: ≥90
- **SEO**: ≥90

### Page Load Time Targets
- Login Page: <2s
- Dashboard: <3s
- Members List: <3s
- Donations List: <3s
- Events List: <3s

## Browser Compatibility Matrix

Test on these browsers:

| Browser | Version | Status |
|---------|---------|--------|
| Chrome | Latest | ✅ |
| Firefox | Latest | ✅ |
| Safari | Latest | ✅ |
| Edge | Latest | ✅ |
| Chrome Mobile | Latest | ✅ |
| Safari iOS | Latest | ✅ |

## Visual Regression Testing

Use Percy or Chromatic for visual regression:

```bash
# Install Percy
npm install --save-dev @percy/cli @percy/cypress

# Run visual tests
npx percy exec -- cypress run
```

## Continuous Integration

Tests run automatically on:
- Every pull request
- Every merge to main branch
- Nightly scheduled runs

## Reporting Issues

When reporting UI/UX issues, include:
1. Page URL
2. Browser and version
3. Screen resolution
4. Steps to reproduce
5. Expected behavior
6. Actual behavior
7. Screenshot/screen recording

## Next Steps

1. **Deploy application** to staging environment
2. **Install Node.js dependencies**: `npm install`
3. **Run automated tests**: `npx cypress run`
4. **Execute manual testing checklist** above
5. **Run Lighthouse audits** on all improved pages
6. **Document any issues** found
7. **Fix and retest** until all checks pass

## Contact

For questions about testing, refer to:
- `/workspace/TESTING.md` - General testing documentation
- `/workspace/cypress/` - E2E test specifications
- `/workspace/tests/` - Feature and unit tests
