import React from 'react'
import {shallow, mount} from 'enzyme'

import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {Overview} from './overview.jsx'
import {
  QUIZ_SUMMATIVE,
  SHUFFLE_ALWAYS,
  SHOW_CORRECTION_AT_DATE,
  SHOW_SCORE_AT_CORRECTION
} from './../enums'

describe('<Overview/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    renew(Overview, 'Overview')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(Overview, {
        quiz: {
          meta: {}
        }
      })
    )

    ensure.missingProps('Overview', [
      'empty',
      'editable',
      'quiz.parameters'
    ])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(Overview, {
        empty: [],
        editable: [],
        quiz: true
      })
    )

    ensure.invalidProps('Overview', [
      'empty',
      'editable',
      'quiz'
    ])
  })

  it('renders an expandable table with quiz properties', () => {
    const overview = mount(
      React.createElement(Overview, {
        empty: true,
        editable: true,
        quiz: {
          description: 'DESC',
          parameters: {
            type: QUIZ_SUMMATIVE,
            showMetadata: true,
            randomOrder: SHUFFLE_ALWAYS,
            randomPick: SHUFFLE_ALWAYS,
            pick: 3,
            duration: 10,
            maxAttempts: 5,
            interruptible: true,
            showCorrectionAt: SHOW_CORRECTION_AT_DATE,
            correctionDate: '2015/05/12',
            anonymizeAttempts: true,
            showScoreAt: SHOW_SCORE_AT_CORRECTION
          },
          meta: {
            created: '2016-12-12',
            published: true,
            editable: true
          }
        }
      })
    )

    ensure.propTypesOk()
    ensure.equal(overview.find('table').length, 1)
    ensure.equal(overview.find('tr').length, 3)
    ensure.equal(overview.find('td').at(1).text(), '2015/05/12')

    const toggle = overview.find('.toggle-exercise-info')
    toggle.simulate('click')
    ensure.equal(overview.find('tr').length, 10)
  })
})
