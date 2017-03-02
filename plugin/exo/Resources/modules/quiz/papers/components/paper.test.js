import React from 'react'
import {mount} from 'enzyme'
import configureMockStore from 'redux-mock-store'
import merge from 'lodash/merge'
import {spyConsole, renew, ensure, mockTranslator} from './../../../utils/test'
import {registerItemType} from './../../../items/item-types'
import {Paper} from './paper.jsx'

describe('<Paper/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Paper, 'Paper')
    mockTranslator()
  })
  afterEach(spyConsole.restore)

  it('renders a paper', () => {
    registerFixtureType({name: 'foo', type: 'foo/bar'})
    registerFixtureType({name: 'baz', type: 'baz/quz'})

    const store = configureMockStore()({
      papers: {
        papers: [
          {
            id: '123',
            number: 1,
            structure: {
              steps: [
                {
                  id: '456',
                  items: [
                    {
                      id: '456',
                      type: 'foo/bar',
                      content: 'Foo?'
                    },
                    {
                      id: '789',
                      type: 'baz/quz',
                      content: 'Bar?'
                    }
                  ]
                }
              ]
            },
            answers: []
          }
        ],
        current: '123'
      }
    })

    mount(<Paper store={store}/>)
    ensure.propTypesOk()
  })
})

function registerFixtureType(properties = {}) {
  return registerItemType(merge(
    {
      name: 'foo',
      type: 'foo/bar',
      editor: {
        component: () => null,
        reduce: item => item
      },
      player: {
        component: () => null,
        reduce: item => item
      },
      paper: () => null
    },
    properties
  ))
}
