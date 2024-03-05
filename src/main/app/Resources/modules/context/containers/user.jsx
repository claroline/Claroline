import {connect} from 'react-redux'

import {ContextUser as ContextUserComponent} from '#/main/app/context/components/user'
import {selectors} from '#/main/app/context/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'

const ContextUser = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state),
    currentUser: securitySelectors.currentUser(state),
    impersonated: selectors.impersonated(state),
    roles: selectors.roles(state),

    registration: configSelectors.param(state, 'selfRegistration'),
  })
)(ContextUserComponent)

export {
  ContextUser
}
