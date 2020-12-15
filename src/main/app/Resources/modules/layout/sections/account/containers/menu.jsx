import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {AccountMenu as AccountMenuComponent} from '#/main/app/layout/sections/account/components/menu'

const AccountMenu = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(AccountMenuComponent)

export {
  AccountMenu
}
