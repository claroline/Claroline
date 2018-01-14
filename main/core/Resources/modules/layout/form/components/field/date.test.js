import {
  ensure,
  describeComponent,
  mountComponent
} from '#/main/core/scaffolding/tests'

import {Date as DateField} from './date.jsx'

describeComponent('Date', DateField,
  // required props
  [
    'id',
    'onChange'
  ],
  // invalid props
  {
    id: 123,
    value: false,
    onlyButton: 'test',
    onChange: 'foo'
  },
  // valid props
  {
    id: '123',
    value: '2012-09-01',
    onChange: () => {}
  },
  // custom tests
  () => {
    it('renders a clickable input', () => {
      const date = mountComponent(DateField, 'Date', {
        id: '123',
        value: '2012-09-01',
        onChange: () => {}
      })

      // checks propTypes
      ensure.propTypesOk()

      const container = date.find('div')
      ensure.equal(container.length, 1)
      const input = container.find('input[type="text"]')
      ensure.equal(input.length, 1)

      input.simulate('click')

      ensure.equal(container.hasClass('react-datepicker__tether-enabled'), true)
    })
  }
)
