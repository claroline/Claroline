import React from 'react'
import {mount} from 'enzyme'

import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {ColorPicker} from './color-picker.jsx'

describe('<ColorPicker/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    renew(ColorPicker, 'ColorPicker')
  })
  afterEach(spyConsole.restore)

  it('renders a clickable widget opening a color picker', () => {
    const picker = mount(
      React.createElement(ColorPicker, {
        id: 'PICKER-ID',
        color: 'blue',
        onPick: () => {}
      })
    )
    ensure.propTypesOk()

    const button = picker.find('.color-picker')
    ensure.equal(button.length, 1)

    button.simulate('click')
    //ensure.equal(picker.find('.twitter-picker').length, 1)
  })
})
