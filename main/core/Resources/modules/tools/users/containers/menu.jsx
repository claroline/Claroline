import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {UsersMenu as UsersMenuComponent} from '#/main/core/tools/users/components/menu'

const UsersMenu = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    workspace: toolSelectors.contextData(state)
  })
)(UsersMenuComponent)

export {
  UsersMenu
}
