describe('Contextual Help', () => {
  beforeEach(() => {
    // Visit a page that has contextual help
    cy.visit('/dashboard');
    cy.wait(1000); // Wait for page to load
  });

  it('should open and close help panel', () => {
    // Click help button
    cy.get('.help-button').should('be.visible').click();
    
    // Check help panel is open
    cy.get('.help-panel').should('be.visible');
    cy.get('.help-panel h3').should('contain', 'Help & Resources');
    
    // Close help panel
    cy.get('.help-panel button[aria-label="Close"]').click();
    cy.get('.help-panel').should('not.exist');
  });

  it('should show contextual help for current page', () => {
    cy.get('.help-button').click();
    
    // Should show contextual help for dashboard
    cy.get('.help-panel').within(() => {
      cy.contains('Dashboard').should('be.visible');
      cy.get('ul li').should('have.length.at.least', 1);
    });
  });

  it('should open help item modal when clicking contextual help', () => {
    cy.get('.help-button').click();
    
    // Click first contextual help item
    cy.get('.help-panel ul li button').first().click();
    
    // Check modal opens
    cy.get('[role="dialog"]').should('be.visible');
    cy.get('[role="dialog"] h2').should('contain', 'Customizing Your Dashboard');
    
    // Close modal
    cy.get('[role="dialog"] button').contains('Close').click();
    cy.get('[role="dialog"]').should('not.exist');
  });

  it('should open keyboard shortcuts modal', () => {
    cy.get('.help-button').click();
    
    // Click keyboard shortcuts
    cy.get('.help-panel').within(() => {
      cy.contains('Keyboard Shortcuts').click();
    });
    
    // Check keyboard shortcuts modal opens
    cy.get('[role="dialog"]').should('be.visible');
    cy.get('[role="dialog"] h2').should('contain', 'Keyboard Shortcuts');
    
    // Should have keyboard shortcut groups
    cy.get('.keyboard-shortcuts .grid > div').should('have.length.at.least', 1);
    
    // Close modal
    cy.get('[role="dialog"] button').contains('Close').click();
    cy.get('[role="dialog"]').should('not.exist');
  });

  it('should open help center in new tab', () => {
    cy.get('.help-button').click();
    
    // Stub window.open to prevent actual navigation
    cy.window().then((win) => {
      cy.stub(win, 'open').as('windowOpen');
    });
    
    // Click help center
    cy.get('.help-panel').within(() => {
      cy.contains('Open Help Center').click();
    });
    
    // Verify window.open was called with correct URL
    cy.get('@windowOpen').should('have.been.calledWith', '/help', '_blank');
  });

  it('should open documentation in new tab', () => {
    cy.get('.help-button').click();
    
    // Stub window.open
    cy.window().then((win) => {
      cy.stub(win, 'open').as('windowOpen');
    });
    
    // Click documentation
    cy.get('.help-panel').within(() => {
      cy.contains('Documentation').click();
    });
    
    // Verify window.open was called with correct URL
    cy.get('@windowOpen').should('have.been.calledWith', '/docs/README-onboarding.md', '_blank');
  });

  it('should open video tutorials in new tab', () => {
    cy.get('.help-button').click();
    
    // Stub window.open
    cy.window().then((win) => {
      cy.stub(win, 'open').as('windowOpen');
    });
    
    // Click video tutorials
    cy.get('.help-panel').within(() => {
      cy.contains('Video Tutorials').click();
    });
    
    // Verify window.open was called with YouTube search URL
    cy.get('@windowOpen').should('have.been.calledWith', 
      'https://www.youtube.com/results?search_query=church+management+system+tutorial', 
      '_blank'
    );
  });

  it('should compose support email with page context', () => {
    cy.get('.help-button').click();
    
    // Stub window.location.href
    cy.window().then((win) => {
      cy.stub(win.location, 'href', 'set').as('locationHref');
    });
    
    // Click contact support
    cy.get('.help-panel').within(() => {
      cy.contains('Contact Support').click();
    });
    
    // Verify mailto URL was constructed
    cy.get('@locationHref').should('have.been.called');
    cy.get('@locationHref').then((stub) => {
      const callArgs = stub.getCall(0).args[0];
      expect(callArgs).to.include('mailto:support@yourchurch.org');
      expect(callArgs).to.include('subject=Support Request - Contextual Help');
      expect(callArgs).to.include('body=');
    });
  });

  it('should dispatch demo event when starting demo', () => {
    cy.get('.help-button').click();
    
    // Click first contextual help item
    cy.get('.help-panel ul li button').first().click();
    
    // Listen for custom event
    cy.window().then((win) => {
      cy.stub(win, 'dispatchEvent').as('dispatchEvent');
    });
    
    // Click "Show Me How" button if available
    cy.get('[role="dialog"]').then(($modal) => {
      if ($modal.find('button').text().includes('Show Me How')) {
        cy.get('[role="dialog"] button').contains('Show Me How').click();
        
        // Verify custom event was dispatched
        cy.get('@dispatchEvent').should('have.been.calledWith', 
          Cypress.sinon.match.instanceOf(CustomEvent)
        );
      }
    });
  });

  it('should restart onboarding when clicked', () => {
    cy.get('.help-button').click();
    
    // Stub window.location.reload
    cy.window().then((win) => {
      cy.stub(win.location, 'reload').as('reload');
    });
    
    // Click restart onboarding
    cy.get('.help-panel').within(() => {
      cy.contains('Restart Onboarding Guide').click();
    });
    
    // Verify reload was called
    cy.get('@reload').should('have.been.called');
  });

  it('should show different contextual help on different pages', () => {
    // Test dashboard contextual help
    cy.get('.help-button').click();
    cy.get('.help-panel').within(() => {
      cy.contains('Dashboard').should('be.visible');
    });
    cy.get('.help-button').click(); // Close panel
    
    // Navigate to members page
    cy.visit('/members');
    cy.wait(1000);
    
    // Check members contextual help
    cy.get('.help-button').click();
    cy.get('.help-panel').within(() => {
      cy.contains('Members').should('be.visible');
    });
  });

  it('should handle help panel keyboard navigation', () => {
    cy.get('.help-button').click();
    
    // Tab through help panel items
    cy.get('.help-panel').within(() => {
      cy.get('button, a').first().focus().should('be.focused');
      cy.get('button, a').first().tab();
      cy.get('button, a').eq(1).should('be.focused');
    });
  });

  it('should close help panel with escape key', () => {
    cy.get('.help-button').click();
    cy.get('.help-panel').should('be.visible');
    
    // Press escape key
    cy.get('body').type('{esc}');
    cy.get('.help-panel').should('not.exist');
  });
});

