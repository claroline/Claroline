import {makeReducer} from '#/main/core/scaffolding/reducer'

const reducer = {
  user: makeReducer({}, {}),
  evaluations: makeReducer({}, {})
}

export {
  reducer
}
