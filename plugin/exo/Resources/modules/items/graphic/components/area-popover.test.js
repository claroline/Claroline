import React from 'react'
import {mount} from 'enzyme'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {AreaPopover} from './area-popover.jsx'

describe('<AreaPopover/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(AreaPopover, 'AreaPopover')
  })
  afterEach(spyConsole.restore)

  it('renders a popover', () => {
    const popover = mount(
      <AreaPopover
        left={10}
        top={50}
        color="red"
        score={3}
        feedback="FEEDBACK"
        onChangeScore={() => {}}
        onChangeFeedback={() => {}}
        onPickColor={() => {}}
        onClose={() => {}}
        onDelete={() => {}}
      />
    )
    ensure.propTypesOk()
    ensure.equal(popover.find('.popover').length, 1)
  })
})
