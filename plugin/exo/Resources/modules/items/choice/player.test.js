import React from 'react'
import {mount} from 'enzyme'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {ChoicePlayer} from './player.jsx'

describe('<ChoicePlayer/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ChoicePlayer, 'ChoicePlayer')
  })
  afterEach(spyConsole.restore)

  it('renders a choice player and dispatches answers', () => {
    let answer = null

    const player = mount(
      <ChoicePlayer
        item={{
          id: 'ID',
          multiple: true,
          random: false,
          numbering: 'none',
          choices: [
            {id: '1', data: 'value1'},
            {id: '2', data: 'value2'}
          ]
        }}
        onChange={(value) => {
          answer = value
        }}
      />
    )

    ensure.propTypesOk()
    ensure.equal(player.find('input[type="checkbox"]').length, 2, 'has checkboxes')
    const chk = player.find('input[type="checkbox"]#2')
    chk.simulate('change', {target: {checked: true}})
    ensure.equal(answer, ['2'])
  })
})
