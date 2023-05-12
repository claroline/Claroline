import {createSelector} from 'reselect'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'privacy'
//const FORM_NAME = STORE_NAME+'.parameters'

const privacyForm = createSelector(
  formSelectors.form,
  form => form[STORE_NAME]
)

const store = (state) => state[STORE_NAME]

/*
const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))
*/

const availableLocales = createSelector(
  [store],
  (store) => store.availableLocales
)

const lockedParameters = createSelector(
  [store],
  (store) => store.lockedParameters
)

const parameters = createSelector(
  [store],
  (store) => store.parameters
)


const locales = createSelector(
  [parameters],
  (parameters) => parameters.locales
)

export const selectors = {
  STORE_NAME,
  privacyForm,
  store,
  availableLocales,
  lockedParameters,
  parameters,
  locales
}
