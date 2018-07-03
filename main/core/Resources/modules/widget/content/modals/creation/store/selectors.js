import {select as formSelectors} from '#/main/core/data/form/selectors'

const STORE_NAME = 'widgetContentCreation'
const FORM_NAME  = `${STORE_NAME}.instance`

const saveEnabled = (state) => formSelectors.saveEnabled(formSelectors.form(state, FORM_NAME))
const instance = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const availableWidgets = (state) => state[STORE_NAME].availableTypes

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  instance,
  saveEnabled,
  availableWidgets
}
