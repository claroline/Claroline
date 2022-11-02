import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/community/tools/community/store'
import {GroupTab as GroupTabComponent} from '#/main/community/tools/community/group/components/tab'
import {actions, selectors} from '#/main/community/tools/community/group/store'

const GroupTab = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canRegister: communitySelectors.canCreate(state)
  }),
  dispatch => ({
    open(id = null) {
      dispatch(actions.open(selectors.FORM_NAME, id))
    },
    addGroupsToRoles(roles, groups) {
      roles.map(role => dispatch(actions.addGroupsToRole(role, groups)))
    }
  })
)(GroupTabComponent)

export {
  GroupTab
}
