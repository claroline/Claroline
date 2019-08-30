import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/core/tools/community/store'
import {GroupTab as GroupTabComponent} from '#/main/core/tools/community/group/components/tab'
import {actions, selectors} from '#/main/core/tools/community/group/store'

const GroupTab = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canCreate: communitySelectors.canCreate(state),
    canRegister: communitySelectors.canRegister(state),
    defaultRole: communitySelectors.defaultRole(state)
  }),
  dispatch => ({
    open(id = null, defaultRole) {
      const defaultValue = {
        organization: null, // retrieve it with axel stuff
        roles: [defaultRole]
      }

      dispatch(actions.open(selectors.FORM_NAME, id, defaultValue))
    },
    addGroupsToRoles(roles, groups) {
      roles.map(role => dispatch(actions.addGroupsToRole(role, groups)))
    }
  })
)(GroupTabComponent)

export {
  GroupTab
}
