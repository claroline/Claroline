import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as communitySelectors} from '#/main/community/tools/community/store'

import {UserList as UserListComponent} from '#/main/community/tools/community/user/components/list'
import {actions, selectors} from '#/main/community/tools/community/user/store'

const UserList = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    canRegister: communitySelectors.canCreate(state),
    canAdministrate: hasPermission('edit', toolSelectors.toolData(state)),
    limitReached: selectors.limitReached(state)
  }),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(actions.unregister(users, workspace))
    },
    addUsersToRoles(roles, users) {
      roles.map(role => dispatch(actions.addUsersToRole(role, users)))
    }
  })
)(UserListComponent)

export {
  UserList
}
