import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'resource'

const FORM_NAME = `${STORE_NAME}.bookReference`

const resource = (state) => state[STORE_NAME]

const bookReferenceForm = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))
const bookReferenceOriginal = (state) => formSelectors.originalData(formSelectors.form(state, FORM_NAME))

const bookReferenceId = createSelector(
  [bookReferenceForm],
  (bookReferenceForm) => bookReferenceForm.id
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  resource,
  bookReferenceId,
  bookReferenceForm,
  bookReferenceOriginal
}
