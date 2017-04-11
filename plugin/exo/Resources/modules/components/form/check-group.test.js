import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {CheckGroup} from './check-group.jsx'

describe('<CheckGroup/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(CheckGroup, 'CheckGroup')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<CheckGroup/>)
    ensure.missingProps(
      'CheckGroup',
      ['checkId', 'label', 'checked', 'onChange']
    )
  })

  it('has typed props', () => {
    shallow(
      <CheckGroup
        checkId={true}
        label={123}
        checked={[]}
        onChange="foo"
      />
    )
    ensure.invalidProps(
      'CheckGroup',
      ['checkId', 'label', 'checked', 'onChange']
    )
  })

  it('renders a checkbox with a label', () => {
    const group = shallow(
      <CheckGroup
        checkId="ID"
        label="LABEL"
        checked={true}
        onChange={() => {}}
      />
    )
    ensure.propTypesOk()
    ensure.equal(group.name(), 'div')
    ensure.equal(group.hasClass('form-group'), true)
    ensure.equal(group.children().length, 1)

    const inputContainer = group.childAt(0)
    const input = inputContainer.find('input[type="checkbox"]#ID')
    ensure.equal(input.length, 1)

    const label = inputContainer.find('label')
    ensure.equal(label.name(), 'label')
    ensure.equal(label.props().htmlFor, 'ID')
  })

  it('displays an help text if any', () => {
    const group = shallow(
      <CheckGroup
        checkId="ID"
        label="LABEL"
        help="HELP"
        checked={true}
        onChange={() => {}}
      />
    )
    ensure.propTypesOk()
    ensure.equal(group.find('span#help-ID.help-block').text(), 'HELP')
  })

  it('calls onChange with boolean value', () => {
    let isChecked = false
    const group = mount(
      <CheckGroup
        checkId="ID"
        label="LABEL"
        checked={isChecked}
        onChange={checked => isChecked = checked}
      />
    )
    ensure.propTypesOk()
    const input = group.find('input[type="checkbox"]#ID')
    ensure.equal(input.length, 1)
    input.simulate('change', {target: {checked: true}})
    ensure.equal(isChecked, true)
  })
})
