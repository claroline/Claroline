import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as layoutSelectors} from '#/main/app/layout/store'

import {PublicMenu as PublicMenuComponent} from '#/main/app/contexts/public/components/menu'
import {selectors as contextSelectors} from '#/main/app/context/store'

const PublicMenu = connect(
  (state) => ({
    selfRegistration: configSelectors.param(state, 'selfRegistration'),
    authenticated: securitySelectors.isAuthenticated(state),
    unavailable: layoutSelectors.unavailable(state),

    //basePath: contextSelectors.path(state),
    tools: contextSelectors.tools(state),
    //shortcuts: contextSelectors.shortcuts(state)
  })
)(PublicMenuComponent)

export {
  PublicMenu
}
