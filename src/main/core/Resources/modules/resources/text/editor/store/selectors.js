import get from 'lodash/get'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {selectors as textSelectors} from '#/main/core/resources/text/store/selectors'

const STORE_NAME = `${textSelectors.STORE_NAME}.editor`
const FORM_NAME = `${STORE_NAME}.textForm`

const text = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))
const availablePlaceholders = (state) => get(state, STORE_NAME+'.availablePlaceholders', [])

export const selectors = {
  FORM_NAME,

  text,
  availablePlaceholders
}
