import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const FORM_NAME = 'parameters'

const availableLocales = state => state.availableLocales
const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))
const locales = createSelector(
  [parameters],
  (parameters) => parameters.locales
)

const archives = createSelector(
  [parameters],
  (parameters) => parameters.archives
)

const plugins = state => state.plugins

export const selectors = {
  FORM_NAME,
  availableLocales,
  parameters,
  locales,
  plugins,
  archives
}
