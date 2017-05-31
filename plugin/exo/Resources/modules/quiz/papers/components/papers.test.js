import React from 'react'
import {mount} from 'enzyme'
import configureMockStore from 'redux-mock-store'

import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {resourceNodeFixture} from '#/main/core/layout/resource/tests'

import {Papers} from './papers.jsx'
import {SHOW_SCORE_AT_CORRECTION} from './../../enums'

describe('<Papers/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    renew(Papers, 'Papers')
  })
  afterEach(spyConsole.restore)

  it('renders a list of papers', () => {
    const store = configureMockStore()({
      resourceNode: resourceNodeFixture(),
      quiz: {
        meta: {
          canViewPapers: true
        }
      },
      papers: {
        papers: {
          '123': {
            id: '123',
            number: 1,
            structure: {
              parameters: {
                showScoreAt: SHOW_SCORE_AT_CORRECTION
              },
              steps: [
                {items: []}
              ]
            },
            user: {
              name: 'John Doe'
            },
            startDate: '1986/02/12',
            finished: true
          },
          '456': {
            id: '456',
            number: 2,
            structure: {
              parameters: {
                showScoreAt: SHOW_SCORE_AT_CORRECTION
              },
              steps: [
                {items: []}
              ]
            },
            user: {
              name: 'Jane Doe'
            },
            startDate: '2015/11/03',
            finished: false
          }
        }
      }
    })

    const papers = mount(
      React.createElement(Papers, {
        store: store
      })
    )

    ensure.propTypesOk()
    ensure.equal(papers.find('table').length, 1)
    ensure.equal(papers.find('tr').length, 3) // 2 papers + 1 header line
  })
})
