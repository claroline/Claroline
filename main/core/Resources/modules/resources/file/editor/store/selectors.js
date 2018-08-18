import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as fileSelectors} from '#/main/core/resources/file/store/selectors'

const FORM_NAME = `${fileSelectors.STORE_NAME}.fileForm`

const file = () => formSelectors.data(formSelectors.form(FORM_NAME))

const mimeType = fileSelectors.mimeType

export const selectors = {
  FORM_NAME,
  mimeType,
  file
}
