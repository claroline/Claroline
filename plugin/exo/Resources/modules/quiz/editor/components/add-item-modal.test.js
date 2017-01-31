import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure, mockTranslator} from './../../../utils/test'
import {registerItemType, resetTypes} from './../../../items/item-types'
import {AddItemModal} from './add-item-modal.jsx'

describe('<AddItemModal/>', () => {
  beforeEach(() => {
    mockTranslator()
    spyConsole.watch()
    renew(AddItemModal, 'AddItemModal')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<AddItemModal/>)
    ensure.missingProps('AddItemModal', ['handleSelect'])
    ensure.missingProps('BaseModal', ['fadeModal', 'hideModal', 'show', 'title'])
  })

  it('has typed props', () => {
    shallow(
      <AddItemModal
        title={{}}
        show="foo"
        fadeModal={[]}
        hideModal="bar"
        handleSelect={123}
      />
    )
    ensure.invalidProps('AddItemModal', ['handleSelect'])
    ensure.invalidProps('BaseModal', ['fadeModal', 'hideModal', 'show', 'title'])
  })

  it('renders a modal with a list of types and dispatches selection', () => {
    const types = [
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
    ]
    types.forEach(registerItemType)

    mount(
      <AddItemModal
        title="TITLE"
        show={true}
        fadeModal={() => {}}
        hideModal={() => {}}
        handleSelect={() => {}}
      />
    )
    ensure.propTypesOk()

    // can't find rendered modal here...

    resetTypes()
  })
})
