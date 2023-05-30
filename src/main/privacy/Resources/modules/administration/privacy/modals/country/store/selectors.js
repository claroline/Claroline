import {selectors as formSelectors} from '#/main/app/content/form/store'

const STORE_NAME = 'privacyCountry'

const store = (state) => state[STORE_NAME]
const FORM_NAME = STORE_NAME+'.parameters'
const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))
export const selectors = {
  STORE_NAME,
  parameters,
  store,
  FORM_NAME
}