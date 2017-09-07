import React from 'react'
import {shallow, mount} from 'enzyme'

import {spyConsole, renew, ensure} from '#/main/core/tests'

import {ToggleableSet} from './toggleable-set.jsx'

describe('<ToggleableSet/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ToggleableSet, 'ToggleableSet')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(ToggleableSet)
    )

    ensure.missingProps(
      'ToggleableSet',
      ['showText', 'hideText', 'children']
    )
  })

  it('has typed props', () => {
    shallow(
      React.createElement(ToggleableSet, {
        showText: 123,
        hideText: false
      }, 'Bar')
    )

    ensure.invalidProps(
      'ToggleableSet',
      ['showText', 'hideText']
    )
  })

  it('Renders a link to toggle section', () => {
    const section = mount(
      React.createElement(ToggleableSet, {
        showText: 'Show section',
        hideText: 'Hide section'
      }, 'Bar')
    )

    ensure.propTypesOk()

    // show
    const showLink = section.find('.toggleable-set-toggle').at(0)
    ensure.equal(showLink.name(), 'a')
    ensure.equal(showLink.text(), 'Show section')

    showLink.simulate('click')

    // hide
    const hideLink = section.find('.toggleable-set-toggle').at(0)
    ensure.equal(hideLink.name(), 'a')
    ensure.equal(hideLink.text(), 'Hide section')
  })
})
