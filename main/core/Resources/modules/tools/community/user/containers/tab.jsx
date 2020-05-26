import {connect} from 'react-redux'

import {selectors as listSelectors} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/core/tools/community/store'
import {UserTab as UserTabComponent} from '#/main/core/tools/community/user/components/tab'
import {actions, selectors} from '#/main/core/tools/community/user/store'

const UserTab = connect(
  state => ({
    path: toolSelectors.path(state),
    listQueryString: listSelectors.queryString(listSelectors.list(state, selectors.LIST_NAME)),
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    canCreate: communitySelectors.canCreate(state),
    defaultRole: communitySelectors.defaultRole(state),
    limitReached: selectors.limitReached(state)
  }),
  dispatch => ({
    open(id = null, defaultRole) {
      dispatch(actions.open(selectors.FORM_NAME, id, {
        organization: null, // retrieve it with axel stuff
        roles: defaultRole ? [defaultRole] : []
      }))
    },
    addUsersToRoles(roles, users) {
      roles.map(role => dispatch(actions.addUsersToRole(role, users)))
    }
  })
)(UserTabComponent)

export {
  UserTab
}
