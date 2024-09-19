import {makeFormReducer} from '#/main/app/content/form/store'

const STORE_NAME = 'cancel_session'

const store = (state) => state[STORE_NAME]

const selectors = {
  STORE_NAME,
  store
}

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer,
  selectors
}
