import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/core/tools/community/store'
import {RoleTab as RoleTabComponent} from '#/main/core/tools/community/role/components/tab'
import {actions, selectors} from '#/main/core/tools/community/role/store'

const RoleTab = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canCreate: communitySelectors.canCreate(state)
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
