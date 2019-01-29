import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const parameters = (state) => formSelectors.data(formSelectors.form(state, 'parameters'))

const theme = createSelector(
  [parameters],
  (parameters) => parameters.theme
)

const iconSetChoices = (state) => state.iconSetChoices

export const selectors = {
  parameters,
  theme,
  iconSetChoices
}
