import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions} from '#/main/app/layout/menu/store'
import {selectors as configSelectors} from '#/main/app/config/store'

import {LayoutMain as LayoutMainComponent} from '#/main/app/layout/components/main'
import {selectors} from '#/main/app/layout/store'

const LayoutMain = withRouter(connect(
  (state) => ({
    availableContexts: selectors.availableContexts(state),
    unavailable: selectors.unavailable(state),
    authenticated: securitySelectors.isAuthenticated(state),
    selfRegistration: selectors.selfRegistration(state),
    changePassword: configSelectors.param(state, 'authentication.login.changePassword')
  }),
  (dispatch) => ({
    /**
     * Open/close the main app menu.
     */
    toggleMenu() {
      dispatch(menuActions.toggle())
    }
  })
)(LayoutMainComponent))

export {
  LayoutMain
}