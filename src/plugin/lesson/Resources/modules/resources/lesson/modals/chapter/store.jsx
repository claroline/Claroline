import {makeFormReducer} from '#/main/app/content/form/store/reducer'

const STORE_NAME = 'ChapterForm'

const selectors = {
  STORE_NAME
}
const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer,
  selectors
}
