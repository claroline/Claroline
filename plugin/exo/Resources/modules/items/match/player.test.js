import React from 'react'
import {mount} from 'enzyme'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {MatchPlayer} from './player.jsx'

describe('<MatchPlayer/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(MatchPlayer, 'MatchPlayer')
  })
  afterEach(spyConsole.restore)

  it('renders a match player', () => {
    mount(
      <MatchPlayer
        item={{
          id: 'ID',
          random: false,
          firstSet: [
            {id: '1', type: 'text/html', data: 'value1'},
            {id: '2', type: 'text/html', data: 'value2'}
          ],
          secondSet: [
            {id: '1', type: 'text/html', data: 'value3'},
            {id: '2', type: 'text/html', data: 'value4'}
          ]
        }}
        onChange={() => {}}
      />
    )

    ensure.propTypesOk()
  })
})
