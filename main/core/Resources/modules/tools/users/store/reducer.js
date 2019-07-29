import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/tools/users/store/selectors'
import {reducer as pendingReducer} from '#/main/core/tools/users/pending/store/reducer'
import {reducer as usersReducer} from '#/main/core/tools/users/user/store/reducer'
import {reducer as groupsReducer} from '#/main/core/tools/users/group/store/reducer'
import {reducer as rolesReducer} from '#/main/core/tools/users/role/store/reducer'

const reducer = combineReducers({
  users: usersReducer,
  groups: groupsReducer,
  roles: rolesReducer,
  pending: pendingReducer,
  parameters: makeFormReducer(selectors.STORE_NAME + '.parameters', {}, {
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters
    }),
    initialData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters
    })
  }),
  restrictions: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.restrictions
  })
})

export {
  reducer
}
