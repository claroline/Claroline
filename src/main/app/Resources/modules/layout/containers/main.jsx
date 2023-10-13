import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions} from '#/main/app/layout/menu/store'

import {LayoutMain as LayoutMainComponent} from '#/main/app/layout/components/main'
import {selectors} from '#/main/app/layout/store'

const LayoutMain = connect(
  (state) => ({
    unavailable: selectors.unavailable(state),
    authenticated: securitySelectors.isAuthenticated(state)
  }),
  (dispatch) => ({
    /**
     * Open/close the main app menu.
     */
    toggleMenu() {
      dispatch(menuActions.toggle())
    }
  })
)(LayoutMainComponent)

export {
  LayoutMain
}