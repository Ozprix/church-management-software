/// <reference types="cypress" />

describe('Onboarding Experience', () => {
  beforeEach(() => {
    // Reset localStorage to simulate first-time user
    cy.window().then((win) => {
      win.localStorage.clear();
    });
    
    // Visit the login page
    cy.visit('/login');
    
    // Login with admin credentials
    cy.get('input[name=email]').type('admin@example.com');
    cy.get('input[name=password]').type('password');
    cy.get('button[type=submit]').click();
  });
  
  it('should display onboarding guide for first-time users', () => {
    // Check that onboarding guide is visible
    cy.checkOnboardingVisible();
    
    // Verify onboarding elements
    cy.get('.onboarding-header').should('be.visible');
    cy.get('.onboarding-steps').should('be.visible');
    cy.get('.progress-indicator').should('be.visible');
  });
  
  it('should allow navigation through onboarding steps', () => {
    // Check initial step
    cy.get('.onboarding-step.active').should('contain', 'Welcome');
    
    // Navigate to next step
    cy.get('button.btn-primary').contains('Next').click();
    
    // Verify second step is active
    cy.get('.onboarding-step.active').should('not.contain', 'Welcome');
    
    // Navigate back
    cy.get('button.btn-secondary').contains('Back').click();
    
    // Verify first step is active again
    cy.get('.onboarding-step.active').should('contain', 'Welcome');
  });
  
  it('should allow skipping the onboarding process', () => {
    // Click skip button
    cy.get('button.btn-link').contains('Skip').click();
    
    // Verify redirect to dashboard
    cy.url().should('include', '/dashboard');
    
    // Verify toast message
    cy.checkToast('Onboarding skipped');
  });
  
  it('should complete onboarding when finishing all steps', () => {
    // Navigate through all steps
    let hasNextButton = true;
    
    while (hasNextButton) {
      cy.get('button.btn-primary').then($button => {
        if ($button.text().includes('Next')) {
          cy.wrap($button).click();
        } else if ($button.text().includes('Finish')) {
          cy.wrap($button).click();
          hasNextButton = false;
        } else {
          hasNextButton = false;
        }
      });
    }
    
    // Verify redirect to dashboard
    cy.url().should('include', '/dashboard');
    
    // Verify toast message
    cy.checkToast('Onboarding completed');
    
    // Verify onboarding is not shown on subsequent visits
    cy.reload();
    cy.get('.onboarding-guide').should('not.exist');
  });
  
  it('should show role-specific content in onboarding', () => {
    // Select finance role
    cy.get('select.role-selector').select('finance');
    
    // Verify finance-specific content is shown
    cy.get('.onboarding-steps').should('contain', 'Donations');
    cy.get('.onboarding-steps').should('contain', 'Pledge Campaigns');
    
    // Select membership role
    cy.get('select.role-selector').select('membership');
    
    // Verify membership-specific content is shown
    cy.get('.onboarding-steps').should('contain', 'Members');
    cy.get('.onboarding-steps').should('contain', 'Groups');
  });
});
