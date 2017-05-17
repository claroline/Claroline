import React from 'react'
import {shallow, mount} from 'enzyme'

import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {registerItemType, resetTypes} from './../../../items/item-types'
import {AddItemModal} from './add-item-modal.jsx'

describe('<AddItemModal/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    registerTestTypes()
    renew(AddItemModal, 'AddItemModal')
  })
  afterEach(() => {
    spyConsole.restore()
    resetTypes()
  })

  it('has required props', () => {
    shallow(
      React.createElement(AddItemModal)
    )

    ensure.missingProps('AddItemModal', ['handleSelect'])
    ensure.missingProps('BaseModal', ['fadeModal', 'hideModal', 'show'])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(AddItemModal, {
        title: {},
        show: 'foo',
        fadeModal: [],
        hideModal: 'bar',
        handleSelect: 123
      })
    )
    ensure.invalidProps('AddItemModal', ['handleSelect'])
    ensure.invalidProps('BaseModal', ['fadeModal', 'hideModal', 'show', 'title'])
  })

  it('renders a modal with a list of types and dispatches selection', () => {
    mount(
      React.createElement(AddItemModal, {
        title: 'TITLE',
        show: true,
        fadeModal: () => {},
        hideModal: () => {},
        handleSelect: () => {}
      })
    )
    ensure.propTypesOk()

    // can't find rendered modal here...
  })
})

function registerTestTypes() {
  [
    {
      name: 'foo',
      type: 'foo/bar',
      editor: {
        component: {},
        reduce: () => {}
      },
      player: {
        component: {},
        reduce: () => {}
      },
      paper: {}
    },
    {
      name: 'baz',
      type: 'baz/quz',
      editor: {
        component: {},
        reduce: () => {}
      },
      player: {
        component: {},
        reduce: () => {}
      },
      paper: {}
    }
  ].forEach(registerItemType)
}
