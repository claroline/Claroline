import {makeReducer} from '#/main/app/store/reducer'

const reducer = {
  currentContext: makeReducer({}),
  instance: makeReducer({})
}

export {
  reducer
}
