import { createSelector } from 'reselect'
import { selectors as formSelectors } from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'connection_messages'
const FORM_NAME = STORE_NAME+'.message'

const store = (state) => state[STORE_NAME]

const messages = createSelector(
  [store],
  (store) => store.messages
)

const currentMessage = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  store,
  messages,
  currentMessage
}
