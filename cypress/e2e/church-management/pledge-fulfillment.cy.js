/// <reference types="cypress" />

describe('Pledge Fulfillment Report', () => {
  beforeEach(() => {
    // Login and navigate to the pledge fulfillment report
    cy.login();
    cy.visit('/reports/pledge-fulfillment');
  });
  
  it('should display the pledge fulfillment report', () => {
    // Check that the report is visible
    cy.get('.pledge-fulfillment-report').should('be.visible');
    
    // Verify report title
    cy.get('.pledge-fulfillment-report h3').should('contain', 'Pledge Fulfillment Report');
    
    // Verify summary cards are displayed
    cy.get('.summary-cards').should('be.visible');
    cy.get('.summary-card').should('have.length.at.least', 3);
    
    // Verify pledges table is displayed
    cy.get('.pledges-table').should('be.visible');
    cy.get('.pledges-table th').should('contain', 'Donor');
    cy.get('.pledges-table th').should('contain', 'Campaign');
    cy.get('.pledges-table th').should('contain', 'Amount Pledged');
    cy.get('.pledges-table th').should('contain', 'Amount Received');
    cy.get('.pledges-table th').should('contain', 'Status');
  });
  
  it('should filter pledges by campaign', () => {
    // Select a campaign from the dropdown
    cy.get('select[data-cy="campaign-filter"]').select(1);
    
    // Verify filtered results
    cy.get('.pledges-table').should('be.visible');
    
    // Get the campaign name
    cy.get('select[data-cy="campaign-filter"] option:selected').then(($option) => {
      const campaignName = $option.text().trim();
      
      // Verify all rows contain the selected campaign
      cy.get('.pledges-table tbody tr').each(($row) => {
        cy.wrap($row).should('contain', campaignName);
      });
    });
  });
  
  it('should filter pledges by status', () => {
    // Select a status from the dropdown
    cy.get('select[data-cy="status-filter"]').select('fulfilled');
    
    // Verify filtered results
    cy.get('.pledges-table').should('be.visible');
    
    // Verify all rows contain the selected status
    cy.get('.pledges-table tbody tr').each(($row) => {
      cy.wrap($row).should('contain', 'Fulfilled');
    });
  });
  
  it('should search pledges by donor name', () => {
    // Get the first donor name
    cy.get('.pledges-table tbody tr').first().find('td').eq(0).then(($cell) => {
      const donorName = $cell.text().trim();
      
      // Search for the donor
      cy.get('input[data-cy="search-input"]').type(donorName);
      
      // Verify filtered results
      cy.get('.pledges-table tbody tr').each(($row) => {
        cy.wrap($row).should('contain', donorName);
      });
    });
  });
  
  it('should sort pledges by different columns', () => {
    // Sort by amount pledged (descending)
    cy.get('th[data-cy="sort-amount"]').click();
    
    // Verify sorting order
    cy.get('.pledges-table tbody tr').then(($rows) => {
      // Get amounts from first two rows
      const amount1 = parseFloat($rows.eq(0).find('td[data-cy="amount"]').text().replace('$', '').replace(',', ''));
      const amount2 = parseFloat($rows.eq(1).find('td[data-cy="amount"]').text().replace('$', '').replace(',', ''));
      
      // Verify descending order
      expect(amount1).to.be.at.least(amount2);
    });
    
    // Sort by donor name (ascending)
    cy.get('th[data-cy="sort-donor"]').click();
    
    // Verify sorting order
    cy.get('.pledges-table tbody tr').then(($rows) => {
      // Get names from first two rows
      const name1 = $rows.eq(0).find('td').eq(0).text().trim();
      const name2 = $rows.eq(1).find('td').eq(0).text().trim();
      
      // Verify ascending order
      expect(name1.localeCompare(name2)).to.be.at.most(0);
    });
  });
  
  it('should export the pledge fulfillment report', () => {
    // Click export button
    cy.get('button[data-cy="export-button"]').click();
    
    // Verify export options modal is displayed
    cy.get('.export-options-modal').should('be.visible');
    
    // Select export format
    cy.get('select[data-cy="export-format"]').select('PDF');
    
    // Configure export options
    cy.get('input[data-cy="include-summary"]').check();
    cy.get('input[data-cy="include-charts"]').check();
    
    // Click export button
    cy.get('button').contains('Export').click();
    
    // Verify success message
    cy.checkToast('Export started');
  });
  
  it('should display charts and visualizations', () => {
    // Verify fulfillment chart is displayed
    cy.get('.fulfillment-chart').should('be.visible');
    
    // Verify campaign comparison chart is displayed
    cy.get('.campaign-comparison-chart').should('be.visible');
    
    // Verify progress bars are displayed
    cy.get('.progress-bar').should('be.visible');
  });
  
  it('should show feature tour for first-time users', () => {
    // Clear localStorage to simulate first-time user
    cy.window().then((win) => {
      win.localStorage.removeItem('featureTours');
      win.location.reload();
    });
    
    // Verify feature tour is displayed
    cy.get('.feature-tour').should('be.visible');
    
    // Navigate through tour
    cy.get('.feature-tour-next').click();
    cy.get('.feature-tour-next').click();
    cy.get('.feature-tour-next').click();
    
    // Complete tour
    cy.get('.feature-tour-finish').click();
    
    // Verify tour is marked as completed
    cy.window().then((win) => {
      const featureTours = JSON.parse(win.localStorage.getItem('featureTours') || '{}');
      expect(featureTours.pledgeFulfillmentReport).to.be.true;
    });
  });
  
  it('should show contextual help', () => {
    // Open contextual help
    cy.get('button[data-cy="help-button"]').click();
    
    // Verify help panel is displayed
    cy.get('.contextual-help-panel').should('be.visible');
    
    // Verify pledge-specific help content is shown
    cy.get('.contextual-help-panel').should('contain', 'Pledge Fulfillment');
    cy.get('.contextual-help-panel').should('contain', 'Understanding the Report');
    
    // Close help panel
    cy.get('.contextual-help-close').click();
    cy.get('.contextual-help-panel').should('not.be.visible');
  });
});
