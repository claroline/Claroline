import {createSelector} from 'reselect'
import get from 'lodash/get'
import uniq from 'lodash/uniq'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store/selectors'
import {selectors as editorSelectors} from '#/main/core/resource/editor/store'

const STORE_NAME = 'editor'
const FORM_NAME = `${quizSelectors.STORE_NAME}.editor`
const BANK_NAME = `${FORM_NAME}.bank`

const quiz = (state) => editorSelectors.resource(state)

const quizId = createSelector(
  [quiz],
  (quiz) => quiz.id
)

const quizType = createSelector(
  [quiz],
  (quiz) => get(quiz, 'parameters.type')
)

const steps = createSelector(
  [quiz],
  (quiz) => quiz.steps || []
)

const items = createSelector(
  [steps],
  (steps) => [].concat(...steps.map(step => step.items || []))
)

const numberingType = createSelector(
  [quiz],
  (quiz) => get(quiz, 'parameters.numbering')
)

const questionNumberingType = createSelector(
  [quiz],
  (quiz) => get(quiz, 'parameters.questionNumbering')
)

const hasExpectedAnswers = createSelector(
  [quiz],
  (quiz) => get(quiz, 'parameters.hasExpectedAnswers')
)

const score = createSelector(
  [quiz],
  (quiz) => get(quiz, 'score')
)

const randomPick = createSelector(
  [quiz],
  (quiz) => get(quiz, 'picking.randomPick')
)

const tags = createSelector(
  [items],
  (items) => uniq(items.reduce((tags, item) => tags.concat(item.tags), []))
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  BANK_NAME,

  quizId,
  quizType,
  steps,
  numberingType,
  questionNumberingType,
  randomPick,
  tags,
  score,
  hasExpectedAnswers
}
