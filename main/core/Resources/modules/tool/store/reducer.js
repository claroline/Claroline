import {makeReducer} from '#/main/core/scaffolding/reducer'

const reducer = {
  editable: makeReducer(false, {}),
  context: makeReducer({}, {})
}

export {
  reducer
}
