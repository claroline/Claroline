import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store'

const STORE_NAME = 'contextEditor'
const FORM_NAME = STORE_NAME+'.form'

const store = (state) => state[STORE_NAME]

const contextData = (state) => formSelectors.value(formSelectors.form(state, FORM_NAME), 'data')

const availableTools = createSelector(
  [store],
  (store) => store.availableTools
)

const enabledTools = (state) => formSelectors.value(formSelectors.form(state, FORM_NAME), 'tools')

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  contextData,
  availableTools,
  enabledTools
}
