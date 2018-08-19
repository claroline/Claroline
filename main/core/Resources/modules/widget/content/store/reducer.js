import {makeReducer} from '#/main/app/store/reducer'

const reducer = {
  context: makeReducer({}),
  instance: makeReducer({})
}

export {
  reducer
}
