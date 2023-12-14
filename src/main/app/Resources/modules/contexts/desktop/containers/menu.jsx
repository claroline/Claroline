import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {DesktopMenu as DesktopMenuComponent} from '#/main/app/contexts/desktop/components/menu'

const DesktopMenu = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    impersonated: contextSelectors.impersonated(state),
    roles: contextSelectors.roles(state),

    basePath: contextSelectors.path(state),
    tools: contextSelectors.tools(state),
    shortcuts: contextSelectors.shortcuts(state)
  })
)(DesktopMenuComponent)

export {
  DesktopMenu
}
