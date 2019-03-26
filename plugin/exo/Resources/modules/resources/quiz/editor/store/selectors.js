import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'editor'
const FORM_NAME = 'resource.editor'

const quiz = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const steps = createSelector(
  [quiz],
  (quiz) => quiz.steps || []
)

const numberingType = createSelector(
  [quiz],
  (quiz) => get(quiz, 'parameters.numbering')
)

const randomPick = createSelector(
  [quiz],
  (quiz) => get(quiz, 'picking.randomPick')
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  steps,
  numberingType,
  randomPick
}
