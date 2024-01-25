import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as communitySelectors} from '#/main/community/tools/community/store'

import {actions, selectors} from '#/main/community/tools/community/group/store'
import {GroupList as GroupListComponent} from '#/main/community/tools/community/group/components/list'
import {actions as listActions} from '#/main/app/content/list/store'

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
    registerGroups() {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(communitySelectors.STORE_NAME + '.users.list'))
    }
  })
)(GroupListComponent)

export {
  GroupList
}
