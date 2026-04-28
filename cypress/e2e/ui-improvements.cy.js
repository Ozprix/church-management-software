describe('UI/UX Improvements E2E Tests', () => {
  
  beforeEach(() => {
    cy.loginAsAdmin();
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
      cy.wait(300);
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
        .should('match', /^[A-Z]{2}$/);
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
      cy.get('[data-testid="event-card"]').should('have.length.greaterThan', 0);
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
    });
  });

  describe('Donations', () => {
    it('should apply preset date filters', () => {
      cy.visit('/donations');
      cy.get('[data-testid="date-range-today"]').click();
      cy.get('[data-testid="donation-row"]').should('have.length.greaterThan', 0);
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
          cy.get('[data-testid="sort-amount"]').click();
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
      cy.get('[data-testid="status-badge"]').first().should('be.visible');
    });

    it('should perform bulk actions', () => {
      cy.visit('/admin/users');
      cy.get('[data-testid="bulk-select"]').first().check();
      cy.get('[data-testid="bulk-select"]').eq(1).check();
      cy.get('[data-testid="bulk-delete"]').should('exist');
    });
  });

  describe('Reports Management', () => {
    it('should filter reports by type', () => {
      cy.visit('/reports');
      cy.get('[data-testid="report-type-filter"]').select('Financial');
      cy.get('[data-testid="report-row"]').should('have.length.greaterThan', 0);
    });

    it('should display report icons by category', () => {
      cy.visit('/reports');
      cy.get('[data-testid="report-icon-financial"]').should('exist');
    });

    it('should export report', () => {
      cy.visit('/reports');
      cy.get('[data-testid="export-pdf"]').first().should('exist');
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
      cy.focused().should('have.attr', 'id', 'email');
      cy.get('#password').tab();
      cy.focused().should('have.attr', 'id', 'password');
    });

    it('should maintain focus management in modals', () => {
      cy.visit('/members');
      cy.get('[data-testid="add-member-btn"]').click();
      cy.focused().should('have.attr', 'id', 'member-name');
    });
  });

  describe('Responsive Design', () => {
    it('should adapt to mobile viewport', () => {
      cy.viewport('iphone-13');
      cy.visit('/dashboard');
      cy.get('[data-testid="mobile-menu"]').should('be.visible');
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
      cy.get('[data-testid="member-row"]').should('exist');
    });
  });
});
