import {createSelector} from 'reselect'
import get from 'lodash/get'
import uniq from 'lodash/uniq'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'editor'
const FORM_NAME = 'resource.editor'

const quiz = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

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

const randomPick = createSelector(
  [quiz],
  (quiz) => get(quiz, 'picking.randomPick')
)

const tags = createSelector(
  [items],
  (items) => uniq(Object.keys(items).map(key => items[key]).reduce((tags, item) => [...tags.concat(item.tags)], []))
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  steps,
  numberingType,
  randomPick,
  tags
}
