// https://github.com/testing-library/cypress-testing-library#installation
import '@testing-library/cypress/add-commands'

describe('test', () => {
  beforeEach( () => {
    cy.visit('/')
    cy.wait(5000)
  })

  it('works', () => {
    cy.contains('Connect with your Claroline Connect account:').should('be.visible')

    // use command from cypress-testing-library
    cy.findByText('Connect with your Claroline Connect account:').should('be.visible')
  })
})

