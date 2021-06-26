// https://github.com/testing-library/cypress-testing-library#installation
import '@testing-library/cypress/add-commands'

describe('test', () => {
  beforeEach( () => {
    cy.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
    })
    cy.visit('/')
  })

  it('works', () => {
    cy.contains('Me connecter avec mon compte').should('be.visible')

    // use command from cypress-testing-library
    cy.findByText('Me connecter avec mon compte Claroline Connect :').should('be.visible')
  })
})
