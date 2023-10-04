import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as communitySelectors} from '#/main/community/tools/community/store'

import {actions} from '#/main/community/tools/community/group/store'
import {GroupList as GroupListComponent} from '#/main/community/tools/community/group/components/list'

const GroupList = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    canRegister: communitySelectors.canCreate(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state))
  }),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(actions.unregister(users, workspace))
    },
    addGroupsToRoles(roles, groups) {
      roles.map(role => dispatch(actions.addGroupsToRole(role, groups)))
    }
  })
)(GroupListComponent)

export {
  GroupList
}
