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
    unregisterGroups(groups, workspace) {
      dispatch(actions.unregisterGroups(groups, workspace))
    },
    registerGroups(groups, workspace) {
      dispatch(actions.registerGroups(groups, workspace))
    }
  })
)(GroupListComponent)

export {
  GroupList
}
