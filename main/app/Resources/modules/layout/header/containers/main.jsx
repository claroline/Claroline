import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as walkthroughActions} from '#/main/app/overlays/walkthrough/store'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors, reducer} from '#/main/app/layout/header/store'
import {HeaderMain as HeaderMainComponent} from '#/main/app/layout/header/components/main'

const HeaderMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      // platform parameters
      menus: selectors.menus(state),
      logo: selectors.logo(state),
      title: selectors.title(state),
      subtitle: selectors.subtitle(state),
      display: selectors.display(state),
      count: selectors.count(state),
      helpUrl: selectors.helpUrl(state),
      loginUrl: selectors.loginUrl(state),
      registrationUrl: selectors.registrationUrl(state),
      redirectHome: selectors.redirectHome(state),

      // user related parameters
      currentUser: securitySelectors.currentUser(state) || securitySelectors.fakeUser(state),
      authenticated: securitySelectors.isAuthenticated(state),
      impersonated: securitySelectors.isImpersonated(state),
      locale: selectors.locale(state),
      administration: selectors.administration(state),
      tools: selectors.tools(state),
      notificationTools: selectors.notificationTools(state)
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
