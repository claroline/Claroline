import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

const reducer = {
  workspaces: makeListReducer('workspaces'),
  parameters: makeReducer({}),
  url: makeReducer({})
}

export {
  reducer
}
