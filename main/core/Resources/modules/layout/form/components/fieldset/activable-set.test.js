import {
  ensure,
  describeComponent,
  mountComponent
} from '#/main/core/scaffolding/tests'

import {ActivableSet} from './activable-set.jsx'

describeComponent('ActivableSet', ActivableSet,
  // required props
  [
    'id',
    'label',
    'children'
  ],
  // invalid props
  {
    id: 123,
    label: [],
    labelActivated: [],
    activated: 123,
    onChange: 'func'
  },
  // valid props
  {
    id: 'ID',
    label: 'LABEL',
    children: 'Bar'
  },
  // custom tests
  () => {
    it('renders a checkbox to activate the section', () => {
      const section = mountComponent(ActivableSet, 'ActivableSet', {
        id: 'ID',
        label: 'LABEL',
        children: 'Bar'
      })

      // checks propTypes
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
      const section = mountComponent(ActivableSet, 'ActivableSet', {
        id: 'ID',
        label: 'LABEL',
        activated: true,
        children: 'Bar'
      })

      // checks propTypes
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
  }
)
