import {selectors as fileSelectors} from '#/main/core/resources/file/store/selectors'

const FORM_NAME = `${fileSelectors.STORE_NAME}.fileForm`

const file = (state) => {
  return state.resource.file
}

const mimeType = fileSelectors.mimeType

export const selectors = {
  FORM_NAME,
  mimeType,
  file
}
