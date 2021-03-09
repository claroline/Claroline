import {connect} from 'react-redux'

import {AuthenticationMenu as AuthenticationMenuComponent} from '#/main/authentication/header/authentication/components/menu'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

const AuthenticationMenu = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state),
    registration: configSelectors.param(state, 'selfRegistration')
  })
)(AuthenticationMenuComponent)

export {
  AuthenticationMenu
}
