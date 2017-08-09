import React from 'react'
import {mount} from 'enzyme'
import configureMockStore from 'redux-mock-store'
import merge from 'lodash/merge'

import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {resourceNodeFixture} from '#/main/core/layout/resource/tests'

import {registerItemType} from './../../../items/item-types'
import {Paper} from './paper.jsx'
import {SHOW_SCORE_AT_CORRECTION} from './../../enums'

describe('<Paper/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    renew(Paper, 'Paper')
  })
  afterEach(spyConsole.restore)

  it('renders a paper', () => {
    registerFixtureType({name: 'foo', type: 'foo/bar'})
    registerFixtureType({name: 'baz', type: 'baz/quz'})

    const store = configureMockStore()({
      resourceNode: resourceNodeFixture(),
      quiz: {
        meta: {
          canViewPapers: true
        },
        parameters: {
          showFullCorrection: true
        }
      },
      papers: {
        papers: {
          '123': {
            id: '123',
            finished: true,
            number: 1,
            structure: {
              parameters: {
                showScoreAt: SHOW_SCORE_AT_CORRECTION
              },
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
        },
        current: '123'
      }
    })

    mount(
      React.createElement(Paper, {
        store: store
      })
    )
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
