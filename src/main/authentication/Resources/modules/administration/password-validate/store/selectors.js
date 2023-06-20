import {createSelector} from 'reselect'
import {selectors as paramSelectors} from '#/main/core/administration/parameters/store/selectors'
import {selectors as formSelectors} from "#/main/app/content/form/store";

const STORE_NAME = 'authenticationParameters'
const FORM_NAME = STORE_NAME + ".parameters"

const store = createSelector(
  [paramSelectors.store],
  (baseStore) => baseStore[STORE_NAME])

const passwordValidate = createSelector(
  [store],
  (store) => store.passwordValidate, // name from reducer.js l.8
)

const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  store,
  passwordValidate,
  parameters
}


