import React from 'react'
import {shallow, mount} from 'enzyme'

import {spyConsole, renew, ensure} from '#/main/core/tests'

// note the alias is required for correct props validation
// the underlying library we use also use DatePicker as component name
// so when checking for invalid prop types the library errors makes the tests fail
import {DatePicker as CustomDatePicker} from './date-picker.jsx'

describe('<DatePicker/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(CustomDatePicker, 'CustomDatePicker')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(CustomDatePicker)
    )
    ensure.missingProps('CustomDatePicker', ['onChange'])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(CustomDatePicker, {
        value: false,
        onChange: 'foo'
      })
    )

    ensure.invalidProps('CustomDatePicker', ['value', 'onChange'])
  })

  it('renders a clickable input', () => {
    const date = mount(
      React.createElement(CustomDatePicker, {
        value: '2012-09-01',
        onChange: () => {}
      })
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
