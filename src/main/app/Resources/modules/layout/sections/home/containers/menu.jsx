import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as layoutSelectors} from '#/main/app/layout/store'

import {HomeMenu as HomeMenuComponent} from '#/main/app/layout/sections/home/components/menu'
import {selectors} from '#/main/app/layout/sections/home/store'

const HomeMenu = connect(
  (state) => ({
    selfRegistration: configSelectors.param(state, 'selfRegistration'),
    authenticated: securitySelectors.isAuthenticated(state),
    unavailable: layoutSelectors.unavailable(state),
    homeType: selectors.homeType(state)
  })
)(HomeMenuComponent)

export {
  HomeMenu
}
