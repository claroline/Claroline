import React from 'react'
import {shallow, mount} from 'enzyme'
import configureMockStore from 'redux-mock-store'
import merge from 'lodash/merge'
import {spyConsole, renew, ensure, mockTranslator} from './../../utils/test'
import {Overview} from './overview.jsx'
import {
  QUIZ_SUMMATIVE,
  SHUFFLE_ALWAYS,
  SHOW_CORRECTION_AT_DATE,
  SHOW_SCORE_AT_CORRECTION
} from './../enums'

describe('<Overview/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Overview, 'Overview')
    mockTranslator()
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<Overview store={mockStore()}/>)
    ensure.missingProps(
      'Overview',
      [
        'editable',
        'steps',
        'quiz.parameters'
      ]
    )
  })

  it('has typed props', () => {
    const store = mockStore({
      quiz: {
        description: 456,
        parameters: true,
        meta: true
      },
      steps: 123
    })
    shallow(<Overview store={store}/>)
    ensure.invalidProps(
      'Overview',
      [
        'quiz.description',
        'steps'
      ]
    )
  })

  it('renders an expandable table with quiz properties', () => {
    const store = mockStore({
      quiz: {
        description: 'DESC',
        parameters: {
          type: QUIZ_SUMMATIVE,
          showMetadata: true,
          randomOrder: SHUFFLE_ALWAYS,
          randomPick: SHUFFLE_ALWAYS,
          pick: 3,
          duration: 0,
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
      },
      steps: {}
    })
    const overview = mount(<Overview store={store}/>)

    ensure.propTypesOk()
    ensure.equal(overview.find('table').length, 1)
    ensure.equal(overview.find('tr').length, 2)
    ensure.equal(overview.find('td').at(0).text(), '2015/05/12')

    const toggle = overview.find('.toggle-exercise-info')
    toggle.simulate('click')
    ensure.equal(overview.find('tr').length, 10)
  })
})

function mockStore(state = {}) {
  const mock = configureMockStore()

  return mock(merge({
    quiz: {
      meta: {},
      steps: []
    }
  }, state))
}
