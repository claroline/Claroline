import {createSelector} from 'reselect'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'privacy'
//const FORM_NAME = STORE_NAME+'.parameters'
const store = (state) => state[STORE_NAME]

const availableLocales = createSelector(
  [store],
  (store) => store.availableLocales
)

const lockedParameters = createSelector(
  [store],
  (store) => store.lockedParameters
)

const parameters = (state) => formSelectors.data(formSelectors.form(state, STORE_NAME))

const locales = createSelector(
  [parameters],
  (parameters) => parameters.locales
)

export const selectors = {
  STORE_NAME,
  store,
  availableLocales,
  lockedParameters,
  parameters,
  locales
}
