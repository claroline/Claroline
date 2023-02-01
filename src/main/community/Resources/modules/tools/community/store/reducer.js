import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors as parametersSelectors} from '#/main/core/tool/modals/parameters/store'

import {reducer as activityReducer} from '#/main/community/tools/community/activity/store/reducer'
import {reducer as pendingReducer} from '#/main/community/tools/community/pending/store/reducer'
import {reducer as usersReducer} from '#/main/community/tools/community/user/store/reducer'
import {reducer as groupsReducer} from '#/main/community/tools/community/group/store/reducer'
import {reducer as rolesReducer} from '#/main/community/tools/community/role/store/reducer'
import {reducer as organizationReducer} from '#/main/community/tools/community/organization/store/reducer'
import {reducer as profileReducer} from '#/main/community/tools/community/profile/store/reducer'
import {reducer as teamsReducer} from '#/main/community/tools/community/team/store/reducer'

import {selectors} from '#/main/community/tools/community/store/selectors'

const reducer = combineReducers({
  parameters: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters,
    [makeInstanceAction(FORM_SUBMIT_SUCCESS, parametersSelectors.STORE_NAME)]: (state, action) => action.updatedData.parameters
  }),
  activity: activityReducer,
  users: usersReducer,
  groups: groupsReducer,
  roles: rolesReducer,
  teams: teamsReducer,
  organizations: organizationReducer,
  profile: profileReducer,
  pending: pendingReducer
})

export {
  reducer
}
