import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as layoutSelectors} from '#/main/app/layout/store'

import {PublicMenu as PublicMenuComponent} from '#/main/app/contexts/public/components/menu'
//import {selectors} from '#/main/app/layout/sections/home/store'

const PublicMenu = connect(
  (state) => ({
    selfRegistration: configSelectors.param(state, 'selfRegistration'),
    authenticated: securitySelectors.isAuthenticated(state),
    unavailable: layoutSelectors.unavailable(state),
    //homeType: selectors.homeType(state)
  })
)(PublicMenuComponent)

export {
  PublicMenu
}
