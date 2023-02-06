import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {MenuUser as MenuUserComponent} from '#/main/app/layout/menu/components/user'

const MenuUser = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(MenuUserComponent)

export {
  MenuUser
}
