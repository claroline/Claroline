import React from 'react'
import {shallow, mount} from 'enzyme'

import {spyConsole, renew, ensure} from '#/main/core/tests'

import {ActivableSet} from './activable-set.jsx'

describe('<ActivableSet/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ActivableSet, 'ActivableSet')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(ActivableSet)
    )

    ensure.missingProps(
      'ActivableSet',
      ['id', 'label', 'children']
    )
  })

  it('has typed props', () => {
    shallow(
      React.createElement(ActivableSet, {
        id: 123,
        label: [],
        labelActivated: [],
        activated: 123,
        onChange: 'func'
      }, 'Bar')
    )

    ensure.invalidProps(
      'ActivableSet',
      ['id', 'label', 'labelActivated', 'activated', 'onChange']
    )
  })

  it('renders a checkbox to activate the section', () => {
    const section = mount(
      React.createElement(ActivableSet, {
        id: 'ID',
        label: 'LABEL'
      }, 'Bar')
    )

    ensure.propTypesOk()

    // checks the checkbox has been created
    const checkbox = section.find('input[type="checkbox"]')
    ensure.equal(checkbox.exists(), true)

    // simulates activation
    checkbox.simulate('change', {target: {checked: true}})

    // checks the fields have been added to the DOM
    ensure.equal(section.find('.sub-fields').exists(), true)
  })

  it('renders a checkbox to deactivate the section', () => {
    const section = mount(
      React.createElement(ActivableSet, {
        id: 'ID',
        label: 'LABEL',
        activated: true
      }, 'Bar')
    )

    ensure.propTypesOk()

    // checks the checkbox has been created and is checked
    const checkbox = section.find('input[type="checkbox"]')
    ensure.equal(checkbox.exists(), true)
    ensure.equal(checkbox.is('[checked=true]'), true)
    ensure.equal(section.find('.sub-fields').exists(), true)

    // simulates deactivation
    checkbox.simulate('change', {target: {checked: false}})

    // checks the fields are not in the DOM
    ensure.equal(section.find('.sub-fields').exists(), false)
  })
})
