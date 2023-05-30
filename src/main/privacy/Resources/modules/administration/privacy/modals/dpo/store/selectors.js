import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'privacyDpo'
const store = (state) => state[STORE_NAME]
const FORM_NAME = STORE_NAME+'.parameters'
const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))
export const selectors = {
  STORE_NAME,
  parameters,
  store,
  FORM_NAME
}