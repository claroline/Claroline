import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {selectors as textSelectors} from '#/main/core/resources/text/store/selectors'

const FORM_NAME = textSelectors.STORE_NAME+'.textForm'

const text = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

export const selectors = {
  FORM_NAME,
  text
}
