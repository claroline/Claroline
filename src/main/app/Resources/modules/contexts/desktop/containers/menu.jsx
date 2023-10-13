import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {DesktopMenu as DesktopMenuComponent} from '#/main/app/contexts/desktop/components/menu'

const DesktopMenu = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    showProgression: configSelectors.param(state, 'desktop.showProgression'),
    section: menuSelectors.openedSection(state),
    tools: contextSelectors.tools(state)
  }),
  (dispatch) => ({
    changeSection(section) {
      dispatch(menuActions.changeSection(section))
    }
  })
)(DesktopMenuComponent)

export {
  DesktopMenu
}
