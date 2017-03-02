import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure} from './../../../utils/test'
import {StepForm} from './step-form.jsx'

describe('<StepForm/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(StepForm, 'StepForm')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      <StepForm parameters={{}}/>
    )
    ensure.missingProps('StepForm', [
      'id',
      'title',
      'description',
      'parameters.maxAttempts',
      'onChange'
    ])
  })

  it('has typed props', () => {
    shallow(
      <StepForm
        id={[]}
        title={123}
        description={{}}
        parameters={{maxAttempts: false}}
        onChange="foo"
      />
    )
    ensure.invalidProps('StepForm', [
      'id',
      'title',
      'description',
      'parameters.maxAttempts',
      'onChange'
    ])
  })

  it('renders a form and dispatches changes', () => {
    let updatedValue = null

    const form = mount(
      <StepForm
        id="ID"
        title="TITLE"
        description="DESC"
        parameters={{maxAttempts: 3}}
        onChange={value => updatedValue = value}
      />
    )

    ensure.propTypesOk()
    ensure.equal(form.find('fieldset').length, 1, 'has fieldset')

    const title = form.find('input#step-ID-title')
    ensure.equal(title.length, 1, 'has title input')
    title.simulate('change', {target: {value: 'FOO'}})
    ensure.equal(updatedValue, {title: 'FOO'})

    const attempts = form.find('input#step-ID-maxAttempts')
    ensure.equal(attempts.length, 1, 'has maxAttempts checkbox')
    attempts.simulate('change', {target: {value: 0}})
    ensure.equal(updatedValue, {parameters: {maxAttempts: 0}})
  })
})
