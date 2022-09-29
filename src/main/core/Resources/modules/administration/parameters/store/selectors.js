import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'main_settings'
const FORM_NAME = STORE_NAME+'.parameters'

const store = (state) => state[STORE_NAME]

const availableLocales = createSelector(
  [store],
  (store) => store.availableLocales
)

const lockedParameters = createSelector(
  [store],
  (store) => store.lockedParameters
)

const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const locales = createSelector(
  [parameters],
  (parameters) => parameters.locales
)

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

const icons = createSelector(
  [store],
  (store) => store.icons
)

const mimeTypes = createSelector(
  [icons],
  (icons) => icons.mimeTypes
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  availableLocales,
  lockedParameters,
  parameters,
  locales,
  theme,
  iconSetChoices,
  mimeTypes
}
