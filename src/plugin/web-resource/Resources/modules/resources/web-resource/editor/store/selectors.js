import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {selectors as fileSelectors} from '#/main/core/resources/file/store/selectors'

import {selectors as webResourceSelectors} from '#/plugin/web-resource/resources/web-resource/store/selectors'

const FORM_NAME = webResourceSelectors.STORE_NAME + '.webResourceForm'
const file = () => formSelectors.data(formSelectors.form(FORM_NAME))
const mimeType = fileSelectors.mimeType

export const selectors = {
  FORM_NAME,
  mimeType,
  file
}
