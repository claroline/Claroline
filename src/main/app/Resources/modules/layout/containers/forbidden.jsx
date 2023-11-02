import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {actions, selectors} from '#/main/app/layout/store'
import {LayoutForbidden as LayoutForbiddenComponent} from '#/main/app/layout/components/forbidden'

const LayoutForbidden = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state),
    disabled: selectors.disabled(state),
    maintenance: selectors.maintenance(state),
    maintenanceMessage: selectors.maintenanceMessage(state),
    restrictions: configSelectors.param(state, 'restrictions'),
  }),
  (dispatch) => ({
    reactivate() {
      return dispatch(actions.extend())
    }
  })
)(LayoutForbiddenComponent)

export {
  LayoutForbidden
}
