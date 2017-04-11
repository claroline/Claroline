import React from 'react'
import {shallow, mount} from 'enzyme'

import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {StepForm} from './step-form.jsx'

describe('<StepForm/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    renew(StepForm, 'StepForm')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(StepForm)
    )

    ensure.missingProps('StepForm', [
      'id',
      'title',
      'description',
      'onChange'
    ])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(StepForm, {
        id: [],
        title: 123,
        description: {},
        onChange: 'foo'
      })
    )

    ensure.invalidProps('StepForm', [
      'id',
      'title',
      'description',
      'onChange'
    ])
  })

  it('renders a form and dispatches changes', () => {
    let updatedValue = null

    const form = mount(
      React.createElement(StepForm, {
        id: 'ID',
        title: 'TITLE',
        description: 'DESC',
        onChange: value => updatedValue = value
      })
    )

    ensure.propTypesOk()
    ensure.equal(form.find('fieldset').length, 1, 'has fieldset')

    const title = form.find('#step-ID-title')
    ensure.equal(title.length, 1, 'has title input')
    title.simulate('change', {target: {value: 'FOO'}})
    ensure.equal(updatedValue, {title: 'FOO'})

    const description = form.find('#step-ID-description')
    ensure.equal(description.length, 1, 'has description input')
  })
})
