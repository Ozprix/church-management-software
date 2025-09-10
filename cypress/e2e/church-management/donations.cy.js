/// <reference types="cypress" />

describe('Donation Management', () => {
  beforeEach(() => {
    // Login and navigate to donations page
    cy.visitDonations();
  });
  
  it('should display the donations list', () => {
    // Check that the donations table is visible
    cy.get('.donations-table').should('be.visible');
    
    // Verify table headers
    cy.get('.donations-table th').should('contain', 'Donor');
    cy.get('.donations-table th').should('contain', 'Amount');
    cy.get('.donations-table th').should('contain', 'Date');
    cy.get('.donations-table th').should('contain', 'Payment Method');
    cy.get('.donations-table th').should('contain', 'Category');
  });
  
  it('should allow filtering donations', () => {
    // Check filter controls exist
    cy.get('.filter-controls').should('be.visible');
    
    // Filter by date range
    cy.get('input[name="date_from"]').type('2025-01-01');
    cy.get('input[name="date_to"]').type('2025-05-26');
    cy.get('button').contains('Apply Filters').click();
    
    // Verify filtered results
    cy.get('.donations-table').should('be.visible');
    
    // Filter by category
    cy.get('select[name="category"]').select('Tithe');
    cy.get('button').contains('Apply Filters').click();
    
    // Verify filtered results
    cy.get('.donations-table').should('be.visible');
    
    // Reset filters
    cy.get('button').contains('Reset').click();
    cy.get('input[name="date_from"]').should('have.value', '');
  });
  
  it('should allow adding a new donation', () => {
    // Click add donation button
    cy.get('button').contains('Add Donation').click();
    
    // Verify donation form is displayed
    cy.get('.donation-form').should('be.visible');
    
    // Fill out donation form
    cy.fillDonationForm({
      donorName: 'John Doe',
      amount: '100.00',
      date: '2025-05-26',
      paymentMethod: 'Cash',
      category: 'Tithe',
      notes: 'Test donation'
    });
    
    // Submit form
    cy.get('button[type="submit"]').click();
    
    // Verify success message
    cy.checkToast('Donation added successfully');
    
    // Verify new donation appears in the table
    cy.get('.donations-table').contains('John Doe');
    cy.get('.donations-table').contains('$100.00');
  });
  
  it('should allow editing an existing donation', () => {
    // Click edit button on first donation
    cy.get('.donations-table tbody tr').first().find('button[title="Edit"]').click();
    
    // Verify edit form is displayed
    cy.get('.donation-form').should('be.visible');
    
    // Update donation amount
    cy.get('input[name="amount"]').clear().type('150.00');
    
    // Submit form
    cy.get('button[type="submit"]').click();
    
    // Verify success message
    cy.checkToast('Donation updated successfully');
    
    // Verify updated donation appears in the table
    cy.get('.donations-table').contains('$150.00');
  });
  
  it('should allow deleting a donation', () => {
    // Get the number of rows before deletion
    cy.get('.donations-table tbody tr').then(($rows) => {
      const initialRowCount = $rows.length;
      
      // Click delete button on first donation
      cy.get('.donations-table tbody tr').first().find('button[title="Delete"]').click();
      
      // Confirm deletion in modal
      cy.get('.confirmation-modal button').contains('Delete').click();
      
      // Verify success message
      cy.checkToast('Donation deleted successfully');
      
      // Verify row was removed
      cy.get('.donations-table tbody tr').should('have.length', initialRowCount - 1);
    });
  });
  
  it('should export donations', () => {
    // Click export button
    cy.get('button[title="Export"]').click();
    
    // Verify export options modal is displayed
    cy.get('.export-options').should('be.visible');
    
    // Select export format
    cy.get('select[name="export_format"]').select('CSV');
    
    // Configure export options
    cy.get('input[name="include_header"]').check();
    
    // Click export button
    cy.get('button').contains('Export').click();
    
    // Verify success message
    cy.checkToast('Export started');
  });
  
  it('should show donation insights', () => {
    // Navigate to donation insights
    cy.get('a').contains('Donation Insights').click();
    
    // Verify insights page is displayed
    cy.url().should('include', '/donations/insights');
    cy.get('.donation-insights').should('be.visible');
    
    // Verify charts are displayed
    cy.get('.donation-chart').should('be.visible');
    cy.get('.summary-cards').should('be.visible');
    
    // Verify date range filter works
    cy.get('input[name="date_from"]').type('2025-01-01');
    cy.get('input[name="date_to"]').type('2025-05-26');
    cy.get('button').contains('Apply').click();
    
    // Verify chart updates
    cy.get('.donation-chart').should('be.visible');
  });
  
  it('should show contextual help for donations', () => {
    // Open contextual help
    cy.openContextualHelp();
    
    // Verify donation-specific help content is shown
    cy.get('.help-panel').should('contain', 'Donations');
    cy.get('.help-panel').should('contain', 'Recording Donations');
    
    // Close help panel
    cy.get('.close-button').click();
    cy.get('.help-panel').should('not.be.visible');
  });
});
