import React from 'react'
import {mount} from 'enzyme'

import {spyConsole, renew, ensure} from '#/main/core/scaffolding/tests'

import {MODE_RECT, MODE_CIRCLE} from '#/plugin/exo/items/graphic/constants'
import {ModeSelector} from '#/plugin/exo/items/graphic/components/mode-selector'

describe('<ModeSelector/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ModeSelector, 'ModeSelector')
  })
  afterEach(spyConsole.restore)

  it('renders two buttons and dispatches mode changes', () => {
    let mode = MODE_CIRCLE

    const group = mount(
      <ModeSelector
        currentMode={mode}
        onChange={newMode => mode = newMode}
      />
    )

    ensure.propTypesOk()
    ensure.equal(group.children().length, 2)

    const rectButton = group.childAt(0)
    rectButton.simulate('click')
    ensure.equal(mode, MODE_RECT)
  })
})
