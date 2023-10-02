import {makeFormReducer} from '#/main/app/content/form/store'

const STORE_NAME = 'CardForm'

const selectors = {
  STORE_NAME
}

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer,
  selectors
}
