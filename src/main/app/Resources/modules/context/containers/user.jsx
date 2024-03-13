import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'

import {ContextUser as ContextUserComponent} from '#/main/app/context/components/user'
import {selectors, actions} from '#/main/app/context/store'

const ContextUser = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state),
    currentUser: securitySelectors.currentUser(state),
    impersonated: selectors.impersonated(state),
    roles: selectors.roles(state),

    registration: configSelectors.param(state, 'selfRegistration'),
  }),
  (dispatch) => ({
    changeStatus(currentUser, status) {
      dispatch(actions.changeStatus(currentUser, status))
    }
  })
)(ContextUserComponent)

export {
  ContextUser
}
