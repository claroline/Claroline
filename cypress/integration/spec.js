// https://github.com/testing-library/cypress-testing-library#installation
import '@testing-library/cypress/add-commands'

it('works', () => {
  cy.visit('index.html')
  cy.contains('Page').should('be.visible')

  // use command from cypress-testing-library
  cy.findByText('Page').should('be.visible')
})
