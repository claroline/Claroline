import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {selectors as directorySelectors} from '#/main/core/resources/directory/store/selectors'

const FORM_NAME = directorySelectors.STORE_NAME+'.directoryForm'

const directory = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

export const selectors = {
  FORM_NAME,
  directory
}
