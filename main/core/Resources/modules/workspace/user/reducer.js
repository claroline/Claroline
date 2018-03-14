import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'

import {reducer as pendingReducer} from '#/main/core/workspace/user/pending/reducer'
import {reducer as usersReducer} from '#/main/core/workspace/user/user/reducer'
import {reducer as groupsReducer} from '#/main/core/workspace/user/group/reducer'
import {reducer as rolesReducer} from '#/main/core/workspace/user/role/reducer'
import {reducer as workspaceReducer} from '#/main/core/workspace/user/parameters/reducer'
import {reducer as modalReducer} from '#/main/core/workspace/user/modals/reducer'

const reducer = makePageReducer({}, {
  users: usersReducer,
  groups: groupsReducer,
  roles: rolesReducer,
  pending: pendingReducer,
  workspace: makeReducer({}, {}),
  parameters: workspaceReducer,
  restrictions: makeReducer({}, {}),
  modals: modalReducer
})

export {
  reducer
}
