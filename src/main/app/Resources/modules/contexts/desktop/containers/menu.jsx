import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

import {selectors} from '#/main/app/context/store'
import {DesktopMenu as DesktopMenuComponent} from '#/main/app/contexts/desktop/components/menu'

const DesktopMenu = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    tools: selectors.tools(state)
  })
)(DesktopMenuComponent)

export {
  DesktopMenu
}
