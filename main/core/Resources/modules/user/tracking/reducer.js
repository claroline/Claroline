import {makeReducer} from '#/main/app/store/reducer'

const reducer = {
  user: makeReducer({}, {}),
  evaluations: makeReducer({}, {})
}

export {
  reducer
}
