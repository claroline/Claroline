import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/tools/resources/store/selectors'

const reducer = combineReducers({
  root: makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, 'resource_manager')]: (state, action) => action.toolData.root || null
  }),
  // the resources with no parents (aka WS roots)
  // for other resources, they are mounted inside the parent directory store
  resources: makeListReducer(selectors.LIST_ROOT_NAME)
})

export {
  reducer
}
