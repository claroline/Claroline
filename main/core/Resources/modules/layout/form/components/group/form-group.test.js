import React from 'react'
import {shallow, mount} from 'enzyme'

import {spyConsole, renew, ensure} from '#/main/core/tests'
import {FormGroup} from './form-group.jsx'

describe('<FormGroup/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(FormGroup, 'FormGroup')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(FormGroup)
    )

    ensure.missingProps('FormGroup', [
      'controlId',
      'label',
      'children'
    ])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(FormGroup, {
        controlId: true,
        label: 123,
        warnOnly: '456'
      }, {toto: true})
    )

    ensure.invalidProps('FormGroup', [
      'controlId',
      'label',
      'warnOnly',
      'children'
    ])
  })

  it('renders a label and a given field', () => {
    const group = shallow(
      <FormGroup controlId='ID' label='LABEL' warnOnly={false}>
        <input id='ID' name='NAME' type='text' value='VALUE'/>
      </FormGroup>
    )
    ensure.propTypesOk()
    ensure.equal(group.name(), 'div')
    ensure.equal(group.hasClass('form-group'), true)
    ensure.equal(group.children().length, 2)

    const label = group.childAt(0)
    ensure.equal(label.name(), 'label')
    ensure.equal(label.hasClass('control-label'), true)
    ensure.equal(label.props().htmlFor, 'ID')

    const input = group.childAt(1)
    ensure.equal(input.name(), 'input')
    ensure.equal(input.props().name, 'NAME')
  })

  it('displays an help text if any', () => {
    const group = mount(
      <FormGroup controlId='ID' label='LABEL' help='HELP'>
        <input id='ID' name='NAME' type='text' value='VALUE' />
      </FormGroup>
    )
    ensure.propTypesOk()
    ensure.equal(group.find('.help-block').text(), 'HELP')
  })

  it('displays an error if any', () => {
    const group = mount(
      <FormGroup controlId='ID' label='LABEL' error='ERROR' warnOnly={false}>
        <input id='ID' name='NAME' type='text' value='VALUE'/>
      </FormGroup>
    )

    ensure.propTypesOk()
    ensure.equal(group.find('.help-block').text(), 'ERROR')
  })
})
