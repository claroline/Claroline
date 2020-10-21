import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {AccountMain as AccountMainComponent} from '#/main/app/layout/sections/account/components/main'

const AccountMain = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(AccountMainComponent)

export {
  AccountMain
}
