import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {combineReducers} from '#/main/core/scaffolding/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'

import {reducer as parametersReducer} from '#/main/core/administration/user/parameters/reducer'
import {reducer as usersReducer} from '#/main/core/administration/user/user/reducer'
import {reducer as groupsReducer} from '#/main/core/administration/user/group/reducer'
import {reducer as rolesReducer} from '#/main/core/administration/user/role/reducer'
import {reducer as profileReducer} from '#/main/core/administration/user/profile/reducer'
import {reducer as organizationReducer} from '#/main/core/administration/user/organization/reducer'
import {reducer as locationReducer} from '#/main/core/administration/user/location/reducer'

const reducer = makePageReducer({}, {
  parameters: parametersReducer,
  users: usersReducer,
  groups: groupsReducer,
  roles: rolesReducer,
  locations: locationReducer,
  profile: profileReducer,
  organizations: organizationReducer,
  platformRoles: makeReducer({}, {}),
  workspaces: combineReducers({
    picker: makeListReducer('workspaces.picker')
  })
})

export {
  reducer
}
