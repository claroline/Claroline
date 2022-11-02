import get from 'lodash/get'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {reducer as parametersReducer} from '#/main/community/administration/community/parameters/store/reducer'
import {reducer as usersReducer} from '#/main/community/administration/community/user/store/reducer'
import {reducer as groupsReducer} from '#/main/community/administration/community/group/store/reducer'
import {reducer as rolesReducer} from '#/main/community/administration/community/role/store/reducer'
import {reducer as profileReducer} from '#/main/community/administration/community/profile/store/reducer'
import {reducer as organizationReducer} from '#/main/community/administration/community/organization/store/reducer'

const reducer = combineReducers({
  parameters: parametersReducer,
  users: usersReducer,
  groups: groupsReducer,
  roles: rolesReducer,
  profile: profileReducer,
  organizations: organizationReducer,
  platformRoles: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'community')]: (state, action) => get(action.toolData, 'platformRoles.data', [])
  })
})

export {
  reducer
}
