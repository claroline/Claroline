import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/community/tools/community/store/selectors'
import {reducer as pendingReducer} from '#/main/community/tools/community/pending/store/reducer'
import {reducer as usersReducer} from '#/main/community/tools/community/user/store/reducer'
import {reducer as groupsReducer} from '#/main/community/tools/community/group/store/reducer'
import {reducer as rolesReducer} from '#/main/community/tools/community/role/store/reducer'

const reducer = combineReducers({
  users: usersReducer,
  groups: groupsReducer,
  roles: rolesReducer,
  pending: pendingReducer,
  parameters: makeFormReducer(selectors.STORE_NAME + '.parameters', {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.context.data
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.context.data
    }),
    initialData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.context.data
    })
  }),
  restrictions: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.restrictions || []
  })
})

export {
  reducer
}
