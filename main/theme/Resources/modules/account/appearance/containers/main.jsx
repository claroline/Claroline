import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {AppearanceMain as AppearanceMainComponent} from '#/main/theme/account/appearance/components/main'

const AppearanceMain = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(AppearanceMainComponent)

export {
  AppearanceMain
}
