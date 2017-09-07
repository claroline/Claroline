import React from 'react'
import {shallow} from 'enzyme'

import {spyConsole, renew, ensure} from '#/main/core/tests'

import {HelpBlock} from './help-block.jsx'

describe('<HelpBlock/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(HelpBlock, 'HelpBlock')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(HelpBlock)
    )

    ensure.missingProps('HelpBlock', [
      'help'
    ])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(HelpBlock, {
        help: []
      })
    )

    ensure.invalidProps('HelpBlock', [
      'help'
    ])
  })

  it('renders a help text', () => {
    const block = shallow(React.createElement(HelpBlock, {
      help: 'HELP'
    }))

    ensure.propTypesOk()
    ensure.equal(block.text(), 'HELP')
  })
})
