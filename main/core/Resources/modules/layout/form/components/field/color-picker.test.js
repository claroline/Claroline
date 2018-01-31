import {
  ensure,
  describeComponent,
  mountComponent
} from '#/main/core/scaffolding/tests'

import {ColorPicker} from './color-picker.jsx'

describeComponent('ColorPicker', ColorPicker,
  // required props
  [
    'id',
    'onChange'
  ],
  // invalid props
  {
    id: {},
    value: [],
    onChange: 123,
    colors: true,
    forFontColor: 'invalid',
    autoOpen: 'invalid'
  },
  // valid props
  {
    id: 'ID',
    value: 'blue',
    onChange: () => {}
  },
  // custom tests
  () => {
    it('renders a clickable widget opening a color picker', () => {
      const picker = mountComponent(ColorPicker, 'ColorPicker', {
        id: 'ID',
        value: 'blue',
        onChange: () => {}
      })

      // check propTypes
      ensure.propTypesOk()

      const button = picker.find('.color-picker')
      ensure.equal(button.length, 1)

      button.simulate('click')
      //ensure.equal(picker.find('.twitter-picker').length, 1)
    })
  }
)
