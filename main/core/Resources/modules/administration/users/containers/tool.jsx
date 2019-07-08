import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {UsersTool as UsersToolComponent} from '#/main/core/administration/users/components/tool'

const UsersTool = connect(
  (state) => ({
    isAdmin: securitySelectors.isAdmin(state)
  })
)(UsersToolComponent)

export {
  UsersTool
}
