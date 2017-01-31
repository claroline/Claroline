import React from 'react'
import {mount} from 'enzyme'
import {spyConsole, renew, ensure} from './../../utils/test'
import {ColorPicker} from './color-picker.jsx'

describe('<ColorPicker/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ColorPicker, 'ColorPicker')
  })
  afterEach(spyConsole.restore)

  it('renders a clickable widget opening a color picker', () => {
    const picker = mount(
      <ColorPicker
        id="PICKER-ID"
        color="blue"
        onPick={() => {}}
      />
    )
    ensure.propTypesOk()

    const button = picker.find('[role="button"]')
    ensure.equal(button.length, 1)

    button.simulate('click')
    ensure.equal(picker.find('.twitter-picker').length, 1)
  })
})
