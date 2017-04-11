import freeze from 'deep-freeze'
import {ensure, mockTranslator} from '#/main/core/tests'
import {reduceCorrection} from './reducer'
import {CORRECTION_INIT, QUESTION_CURRENT, SCORE_UPDATE, FEEDBACK_UPDATE, REMOVE_ANSWERS} from './actions'

describe('Correction reducer', () => {
  before(mockTranslator)

  it('returns an empty correction object by default', () => {
    const correction = reduceCorrection(undefined, {})
    ensure.equal(correction, {})
  })

  it('sets correction on init', () => {
    const correction = reduceCorrection({}, {type: CORRECTION_INIT, correction: {questions: 'QUESTIONS', answers: 'ANSWERS'}})
    ensure.equal(correction.questions, 'QUESTIONS')
    ensure.equal(correction.answers, 'ANSWERS')
    ensure.equal(correction.currentQuestionId, null)
  })

  it('updates current question id', () => {
    const state = freeze({currentQuestionId: 'q1', questions: {}, answers: {}})
    const correction = reduceCorrection(state, {type: QUESTION_CURRENT, id: 'q2'})
    ensure.equal(correction, {currentQuestionId: 'q2', questions: {}, answers: {}})
  })

  it('updates an answer score', () => {
    const state = freeze({currentQuestionId: 'q1', questions: {}, answers: [{id: 'a1'}, {id: 'a2'}]})
    const correction = reduceCorrection(state, {type: SCORE_UPDATE, answerId: 'a2', score: '18'})
    ensure.equal(correction, {currentQuestionId: 'q1', questions: {}, answers: [{id: 'a1'}, {id: 'a2', score: '18'}]})
  })

  it('updates an answer feedback', () => {
    const state = freeze({currentQuestionId: 'q1', questions: {}, answers: [{id: 'a1'}, {id: 'a2'}]})
    const correction = reduceCorrection(state, {type: FEEDBACK_UPDATE, answerId: 'a1', feedback: 'feedback'})
    ensure.equal(correction, {currentQuestionId: 'q1', questions: {}, answers: [{id: 'a1', feedback: 'feedback'}, {id: 'a2'}]})
  })

  it('removes answers with valid score', () => {
    const state = freeze({
      currentQuestionId: 'q1',
      questions: [
        {id: 'q1', score: {type: 'manual', max: 20}},
        {id: 'q2', score: {type: 'manual', max: 100}}
      ],
      answers: [
        {id: 'a1', questionId: 'q2', score: '20'},
        {id: 'a2', questionId: 'q1'},
        {id: 'a3', questionId: 'q1', feedback: 'feedback'},
        {id: 'a4', questionId: 'q1', score: 'abc'},
        {id: 'a5', questionId: 'q1', score: '21'},
        {id: 'a6', questionId: 'q1', score: null},
        {id: 'a7', questionId: 'q1', score: '10e'},
        {id: 'a8', questionId: 'q1', score: '--2'},
        {id: 'a9', questionId: 'q1', score: '+'},
        {id: 'a10', questionId: 'q1', score: ''},
        {id: 'a11', questionId: 'q1', score: '   '},
        {id: 'ok1', questionId: 'q1', score: '16'},
        {id: 'ok2', questionId: 'q1', score: '16', feedback: 'feedback'},
        {id: 'ok3', questionId: 'q1', score: '14.56'},
        {id: 'ok4', questionId: 'q1', score: '-7.7'},
        {id: 'ok5', questionId: 'q1', score: '0'},
        {id: 'ok6', questionId: 'q1', score: '0.'},
        {id: 'ok7', questionId: 'q1', score: '.123'},
        {id: 'ok8', questionId: 'q1', score: '-.123'},
        {id: 'ok9', questionId: 'q1', score: '+2'},
        {id: 'ok10', questionId: 'q1', score: '   8   '}
      ]
    })
    const correction = reduceCorrection(state, {type: REMOVE_ANSWERS, questionId: 'q1'})
    ensure.equal(
      correction,
      {
        currentQuestionId: 'q1',
        questions: [
          {id: 'q1', score: {type: 'manual', max: 20}},
          {id: 'q2', score: {type: 'manual', max: 100}}
        ],
        answers: [
          {id: 'a1', questionId: 'q2', score: '20'},
          {id: 'a2', questionId: 'q1'},
          {id: 'a3', questionId: 'q1', feedback: 'feedback'},
          {id: 'a4', questionId: 'q1', score: 'abc'},
          {id: 'a5', questionId: 'q1', score: '21'},
          {id: 'a6', questionId: 'q1', score: null},
          {id: 'a7', questionId: 'q1', score: '10e'},
          {id: 'a8', questionId: 'q1', score: '--2'},
          {id: 'a9', questionId: 'q1', score: '+'},
          {id: 'a10', questionId: 'q1', score: ''},
          {id: 'a11', questionId: 'q1', score: '   '}
        ]
      }
    )
  })
})
