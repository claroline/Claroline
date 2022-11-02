import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {ProfileMain as ProfileMainComponent} from '#/main/community/account/profile/components/main'

const ProfileMain = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(ProfileMainComponent)

export {
  ProfileMain
}
