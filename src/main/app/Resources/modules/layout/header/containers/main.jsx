import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {actions, selectors, reducer} from '#/main/app/layout/header/store'
import {HeaderMain as HeaderMainComponent} from '#/main/app/layout/header/components/main'

const HeaderMain = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
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
        administration: securitySelectors.hasAdministration(state)
      }),
      (dispatch) => ({
        sendValidationEmail() {
          dispatch(actions.sendValidationEmail())
        }
      })
    )(HeaderMainComponent)
  )
)

export {
  HeaderMain
}
