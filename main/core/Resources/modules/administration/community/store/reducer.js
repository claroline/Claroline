import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {reducer as parametersReducer} from '#/main/core/administration/community/parameters/store/reducer'
import {reducer as usersReducer} from '#/main/core/administration/community/user/store/reducer'
import {reducer as groupsReducer} from '#/main/core/administration/community/group/store/reducer'
import {reducer as rolesReducer} from '#/main/core/administration/community/role/store/reducer'
import {reducer as profileReducer} from '#/main/core/administration/community/profile/store/reducer'
import {reducer as organizationReducer} from '#/main/core/administration/community/organization/store/reducer'
import {reducer as locationReducer} from '#/main/core/administration/community/location/store/reducer'

const reducer = combineReducers({
  parameters: parametersReducer,
  users: usersReducer,
  groups: groupsReducer,
  roles: rolesReducer,
  locations: locationReducer,
  profile: profileReducer,
  organizations: organizationReducer,
  platformRoles: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'community')]: (state, action) => action.toolData.platformRoles.data
  }),

  // deprecated
  workspaces: combineReducers({
    picker: makeListReducer('workspaces.picker')
  })
})

export {
  reducer
}
