import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'widgetContentParameters'
const FORM_NAME  = `${STORE_NAME}.instance`

const saveEnabled = (state) => formSelectors.saveEnabled(formSelectors.form(state, STORE_NAME))
const formData = (state) => formSelectors.data(formSelectors.form(state, STORE_NAME))

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  formData,
  saveEnabled
}
