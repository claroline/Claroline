import {select as formSelectors} from '#/main/core/data/form/selectors'

const STORE_NAME = 'tabParameters'

const saveEnabled = (state) => formSelectors.saveEnabled(formSelectors.form(state, STORE_NAME))
const tab = (state) => formSelectors.data(formSelectors.form(state, STORE_NAME))

export const selectors = {
  STORE_NAME,
  saveEnabled,
  tab
}
