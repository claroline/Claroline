import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'appearance_settings'
const FORM_NAME = STORE_NAME+'.parameters'

const store = (state) => state[STORE_NAME]

const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const theme = createSelector(
  [parameters],
  (parameters) => parameters.theme
)

const iconSetChoices = createSelector(
  [store],
  (store) => store.iconSetChoices.reduce((acc, current) => Object.assign(acc, {
    [current]: current
  }), {})
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  parameters,
  theme,
  iconSetChoices
}
