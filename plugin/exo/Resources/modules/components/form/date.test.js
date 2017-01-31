import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure} from './../../utils/test'
import {Date} from './date.jsx'

describe('<Date/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Date, 'Date')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<Date/>)
    ensure.missingProps('Date', ['name', 'onChange'])
  })

  it('has typed props', () => {
    shallow(
      <Date
        name={123}
        value={false}
        onChange="foo"
      />
    )
    ensure.invalidProps('Date', ['name', 'value', 'onChange'])
  })

  it('renders a clickable input', () => {
    const date = mount(
      <Date
        name="NAME"
        value="2012-09-01"
        onChange={() => {}}
      />
    )
    ensure.propTypesOk()

    const container = date.find('div')
    ensure.equal(container.length, 1)
    const input = container.find('input[type="text"]')
    ensure.equal(input.length, 1)

    input.simulate('click')

    ensure.equal(container.hasClass('react-datepicker__tether-enabled'), true)
  })
})
