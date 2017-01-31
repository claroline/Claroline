import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure} from './../../../utils/test'
import {SHUFFLE_ONCE, SHUFFLE_NEVER} from './../../enums'
import {QuizEditor} from './quiz-editor.jsx'

describe('<QuizEditor/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(QuizEditor, 'QuizEditor')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<QuizEditor quiz={{}}/>)
    ensure.missingProps(
      'QuizEditor',
      [
        'quiz.title',
        'validating',
        'updateProperties',
        'activePanelKey',
        'handlePanelClick'
      ]
    )
  })

  it('has typed props', () => {
    shallow(
      <QuizEditor
        quiz="foo"
        validating={[]}
        updateProperties={123}
        activePanelKey={[]}
        handlePanelClick="bar"
      />
    )
    ensure.invalidProps(
      'QuizEditor',
      [
        'quiz',
        'validating',
        'updateProperties',
        'activePanelKey',
        'handlePanelClick'
      ]
    )
  })

  it('renders a form and dispatches changes', () => {
    let updatedPath = null
    let updatedValue = null

    const form = mount(
      <QuizEditor
        quiz={fixture()}
        validating={false}
        updateProperties={(path, value) => {
          updatedPath = path
          updatedValue = value
        }}
        activePanelKey={false}
        handlePanelClick={() => {}}
      />
    )

    ensure.propTypesOk()
    ensure.equal(form.find('form').length, 1, 'has form')

    const title = form.find('input#quiz-title')
    ensure.equal(title.length, 1, 'has title input')
    title.simulate('change', {target: {value: 'FOO'}})
    ensure.equal(updatedPath, 'title')
    ensure.equal(updatedValue, 'FOO')

    const anonymizeAttempts = form.find('input#quiz-anonymizeAttempts')
    ensure.equal(anonymizeAttempts.length, 1, 'has anonymizeAttempts checkbox')
    anonymizeAttempts.simulate('change', {target: {checked: true}})
    ensure.equal(updatedPath, 'parameters.anonymizeAttempts')
    ensure.equal(updatedValue, true)
  })
})

function fixture() {
  return {
    title: 'TITLE',
    description: 'DESC',
    parameters: {
      type: 'type',
      showMetadata: true,
      randomOrder: SHUFFLE_NEVER,
      randomPick: SHUFFLE_ONCE,
      pick: 12,
      duration: 123,
      maxAttempts: 4,
      interruptible: true,
      showCorrectionAt: 'never',
      correctionDate: null,
      anonymizeAttempts: false,
      showScoreAt: 'never',
      showStatistics: true,
      showFullCorrection: false,
      showFeedback: false
    }
  }
}
