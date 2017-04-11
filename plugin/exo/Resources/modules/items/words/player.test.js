import React from 'react'
import {mount} from 'enzyme'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {WordsPlayer} from './player.jsx'

describe('<WordsPlayer/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(WordsPlayer, 'WordsPlayer')
  })
  afterEach(spyConsole.restore)

  it('renders a word player and dispatches answers', () => {

    const player = mount(
      <WordsPlayer
        item={{
          id: 'ID',
          contentType: 'text',
          maxLength: 42
        }}
        onChange={() => {}}
      />
    )

    ensure.propTypesOk()
    const textBox = player.find('div[role="textbox"]')
    ensure.equal(textBox.length, 1)
  })
})
