import {makeFormReducer} from '#/main/app/content/form/store'

const STORE_NAME = 'dropzoneAddDocumentForm'

const selectors = {
  STORE_NAME
}

const reducer = makeFormReducer(STORE_NAME)

export {
  selectors,
  reducer
}
