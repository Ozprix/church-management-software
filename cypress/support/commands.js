// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

// Custom command for login
Cypress.Commands.add('login', (email = 'admin@example.com', password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/login')
    cy.get('input[name=email]').type(email)
    cy.get('input[name=password]').type(password)
    cy.get('button[type=submit]').click()
    cy.url().should('include', '/dashboard')
  }, {
    // Session options
    validate: () => {
      // Check if we're still logged in
      cy.getCookie('laravel_session').should('exist')
    },
    cacheAcrossSpecs: true
  })
})

// Custom command for accessing the donation management page
Cypress.Commands.add('visitDonations', () => {
  cy.login()
  cy.visit('/donations')
  cy.contains('h1', 'Donations').should('be.visible')
})

// Custom command for accessing the member management page
Cypress.Commands.add('visitMembers', () => {
  cy.login()
  cy.visit('/members')
  cy.contains('h1', 'Members').should('be.visible')
})

// Custom command for accessing the pledge campaign page
Cypress.Commands.add('visitPledgeCampaigns', () => {
  cy.login()
  cy.visit('/pledge-campaigns')
  cy.contains('h1', 'Pledge Campaigns').should('be.visible')
})

// Custom command for accessing the reports page
Cypress.Commands.add('visitReports', () => {
  cy.login()
  cy.visit('/reports')
  cy.contains('h1', 'Reports').should('be.visible')
})

// Custom command for accessing the help center
Cypress.Commands.add('visitHelpCenter', () => {
  cy.login()
  cy.visit('/help-center')
  cy.contains('h1', 'Help Center').should('be.visible')
})

// Custom command for dark mode toggle
Cypress.Commands.add('toggleDarkMode', () => {
  cy.get('.dark-mode-toggle').click()
})

// Custom command for checking toast notifications
Cypress.Commands.add('checkToast', (message) => {
  cy.get('.toast-message').should('contain', message)
})

// Custom command for filling out a donation form
Cypress.Commands.add('fillDonationForm', (donationData) => {
  const { donorName, amount, date, paymentMethod, category, notes } = donationData
  
  if (donorName) cy.get('input[name="donor_name"]').clear().type(donorName)
  if (amount) cy.get('input[name="amount"]').clear().type(amount)
  if (date) cy.get('input[name="date"]').clear().type(date)
  if (paymentMethod) cy.get('select[name="payment_method"]').select(paymentMethod)
  if (category) cy.get('select[name="category"]').select(category)
  if (notes) cy.get('textarea[name="notes"]').clear().type(notes)
})

// Custom command for filling out a member form
Cypress.Commands.add('fillMemberForm', (memberData) => {
  const { firstName, lastName, email, phone, address, birthdate, membershipStatus } = memberData
  
  if (firstName) cy.get('input[name="first_name"]').clear().type(firstName)
  if (lastName) cy.get('input[name="last_name"]').clear().type(lastName)
  if (email) cy.get('input[name="email"]').clear().type(email)
  if (phone) cy.get('input[name="phone"]').clear().type(phone)
  if (address) cy.get('textarea[name="address"]').clear().type(address)
  if (birthdate) cy.get('input[name="birthdate"]').clear().type(birthdate)
  if (membershipStatus) cy.get('select[name="membership_status"]').select(membershipStatus)
})

// Custom command for checking if onboarding is visible
Cypress.Commands.add('checkOnboardingVisible', () => {
  cy.get('.onboarding-guide').should('be.visible')
})

// Custom command for completing onboarding
Cypress.Commands.add('completeOnboarding', () => {
  cy.get('.onboarding-guide').then($guide => {
    if ($guide.length) {
      // Click through all steps until the end
      cy.get('.onboarding-guide .btn-primary').click({ multiple: true, force: true })
    }
  })
})

// Custom command for opening contextual help
Cypress.Commands.add('openContextualHelp', () => {
  cy.get('.help-button').click()
  cy.get('.help-panel').should('be.visible')
})

// Custom command for checking export functionality
Cypress.Commands.add('checkExportOptions', () => {
  cy.get('button[title="Export"]').click()
  cy.get('.export-options').should('be.visible')
  cy.get('select[name="export_format"]').should('exist')
})
