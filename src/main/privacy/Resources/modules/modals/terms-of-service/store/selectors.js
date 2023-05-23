import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'privacy'
const FORM_NAME = STORE_NAME+'.parameters'
const store = (state) => state[STORE_NAME]
const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

export const selectors = {
  STORE_NAME,
  store,
  parameters,
  FORM_NAME
}
