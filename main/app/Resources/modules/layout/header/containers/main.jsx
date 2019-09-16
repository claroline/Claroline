import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as walkthroughActions} from '#/main/app/overlays/walkthrough/store'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors, reducer} from '#/main/app/layout/header/store'
import {HeaderMain as HeaderMainComponent} from '#/main/app/layout/header/components/main'

const HeaderMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      // header configuration
      menus: selectors.menus(state),
      display: selectors.display(state),

      // platform parameters
      logo: configSelectors.param(state, 'logo'),
      title: configSelectors.param(state, 'name'),
      subtitle: configSelectors.param(state, 'secondaryName'),
      helpUrl: configSelectors.param(state, 'helpUrl'),
      registration: configSelectors.param(state, 'selfRegistration'),
      locale: configSelectors.param(state, 'locale'),

      // user related parameters
      currentUser: securitySelectors.currentUser(state) || securitySelectors.fakeUser(state),
      authenticated: securitySelectors.isAuthenticated(state),
      impersonated: securitySelectors.isImpersonated(state),
      isAdmin: securitySelectors.isAdmin(state)
    }),
    (dispatch) => ({
      startWalkthrough(steps, additional, documentation) {
        dispatch(walkthroughActions.start(steps, additional, documentation))
      }
    })
  )(HeaderMainComponent)
)

export {
  HeaderMain
}
