import {
  ensure,
  describeComponent,
  mountComponent,
  shallowComponent
} from '#/main/core/scaffolding/tests'

import {CheckGroup} from './check-group.jsx'

describeComponent('CheckGroup', CheckGroup,
  // required props
  [
    'id',
    'label',
    'onChange'
  ],
  // invalid props
  {
    id: true,
    label: 123,
    labelChecked: 123,
    value: [],
    onChange: 'foo',
    help: []
  },
  // valid props
  {
    id: 'ID',
    label: 'LABEL',
    value: true,
    onChange: () => {}
  },
  // custom tests
  () => {
    it('renders a checkbox with a label', () => {
      const group = shallowComponent(CheckGroup, 'CheckGroup', {
        id: 'ID',
        label: 'LABEL',
        value: true,
        onChange: () => {}
      })

      // check propTypes
      ensure.propTypesOk()

      ensure.equal(group.name(), 'div')
      ensure.equal(group.hasClass('form-group'), true)
      ensure.equal(group.children().length, 1)

      ensure.equal(group.find('#ID').exists(), true)
    })

    it('displays an help text if any', () => {
      const group = mountComponent(CheckGroup, 'CheckGroup', {
        id: 'ID',
        label: 'LABEL',
        help: 'HELP',
        value: true,
        onChange: () => {}
      })

      // check propTypes
      ensure.propTypesOk()

      ensure.equal(group.find('.help-block').text(), 'HELP')
    })

    it('calls onChange with boolean value', () => {
      let isChecked = false

      const group = mountComponent(CheckGroup, 'CheckGroup', {
        id: 'ID',
        label: 'LABEL',
        value: true,
        onChange: checked => isChecked = checked
      })

      // check propTypes
      ensure.propTypesOk()

      const input = group.find('input[type="checkbox"]#ID')
      ensure.equal(input.length, 1)

      input.simulate('change', {target: {checked: true}})
      ensure.equal(isChecked, true)
    })
  }
)
