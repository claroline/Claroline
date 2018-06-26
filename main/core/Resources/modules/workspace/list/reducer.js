import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = {
  workspaces: makeListReducer('workspaces', {}),
  parameters: makeReducer({}, {}, {}),
  url: makeReducer({}, {}, {})
}

export {
  reducer
}
