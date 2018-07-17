import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = {
  workspaces: makeListReducer('workspaces', {}),
  parameters: makeReducer({}, {}, {}),
  url: makeReducer({}, {}, {})
}

export {
  reducer
}
