import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/community/tools/community/store'
import {RoleTab as RoleTabComponent} from '#/main/community/tools/community/role/components/tab'
import {actions, selectors} from '#/main/community/tools/community/role/store'

const RoleTab = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canCreate: communitySelectors.canCreateRole(state)
  }),
  dispatch => ({
    open(id = null, workspace) {
      dispatch(actions.open(selectors.FORM_NAME, id, {
        type: 2, // todo : ugly workspace type
        workspace
      }))
    }
  })
)(RoleTabComponent)

export {
  RoleTab
}
