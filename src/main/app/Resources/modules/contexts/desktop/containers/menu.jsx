import {connect} from 'react-redux'

import {selectors as contextSelectors} from '#/main/app/context/store'

import {DesktopMenu as DesktopMenuComponent} from '#/main/app/contexts/desktop/components/menu'

const DesktopMenu = connect(
  (state) => ({
    basePath: contextSelectors.path(state),
    tools: contextSelectors.tools(state),
    shortcuts: contextSelectors.shortcuts(state)
  })
)(DesktopMenuComponent)

export {
  DesktopMenu
}
