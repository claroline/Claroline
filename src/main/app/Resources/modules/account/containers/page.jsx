import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {AccountPage as AccountPageComponent} from '#/main/app/account/components/page'

const AccountPage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(AccountPageComponent)

export {
  AccountPage
}
