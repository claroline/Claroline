import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure, mockTranslator, mockRouting} from '#/main/core/tests'
import {PlayerNav} from './nav-bar.jsx'

describe('<PlayerNav/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    renew(PlayerNav, 'PlayerNav')
    mockRouting()
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(PlayerNav)
    )
    ensure.missingProps('PlayerNav', [
      'step',
      'navigateTo',
      'navigateToAndValidate',
      'finish',
      'openFeedbackAndValidate',
      'submit',
      'showFeedback',
      'feedbackEnabled',
      'currentStepSend'
    ])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(PlayerNav, {
        next: [],
        previous: [],
        navigateTo: {},
        finish: [],
        submit: []
      })
    )
    ensure.invalidProps('PlayerNav', [
      'next',
      'previous',
      'navigateTo',
      'finish',
      'submit'
    ])
  })

  it('renders a navbar', () => {
    const navbar = mount(
      React.createElement(PlayerNav, {
        navigateTo: () => true,
        finish: () => true,
        submit: () => true,
        openFeedbackAndValidate: () => true,
        navigateToAndValidate: () => true,
        step: {id: '1',  items:[]},
        currentStepSend: true,
        showFeedback: false,
        feedbackEnabled: false
      })
    )
    ensure.propTypesOk()
    ensure.equal(navbar.find('.player-nav').length, 1)

    // There is no previous step, so no previous btn
    ensure.equal(navbar.find('.btn-previous').length, 0)

    // There is no next step, so no next btn
    ensure.equal(navbar.find('.btn-validate').length, 0)

    // On the last step there is a finish btn
    ensure.equal(navbar.find('.btn-finish').length, 1)
  })

  it('renders a previous btn if there is a previous step', () => {
    const previousStep = {
      id: '123',
      items: []
    }

    const navbar = mount(
      React.createElement(PlayerNav, {
        previous: previousStep,
        navigateTo: () => true,
        finish: () => true,
        submit: () => true,
        openFeedbackAndValidate: () => true,
        navigateToAndValidate: () => true,
        step: {id: '1',  items:[]},
        currentStepSend: true,
        showFeedback: true,
        feedbackEnabled: true
      })
    )
    ensure.propTypesOk()
    ensure.equal(navbar.find('.btn-previous').length, 1)
  })

  it('renders a next btn if there is a next step', () => {
    const nextStep = {
      id: '123',
      items: []
    }

    const navbar = mount(
      React.createElement(PlayerNav, {
        next: nextStep,
        navigateTo: () => true,
        finish: () => true,
        submit: () => true,
        openFeedbackAndValidate: () => true,
        navigateToAndValidate: () => true,
        step: {id: '1',  items:[]},
        currentStepSend: true,
        showFeedback: true,
        feedbackEnabled: true
      })
    )

    ensure.propTypesOk()
    ensure.equal(navbar.find('.btn-next').length, 1)
    // Finish btn is only shown if there is no next step
    ensure.equal(navbar.find('.btn-finish').length, 0)
  })
})
