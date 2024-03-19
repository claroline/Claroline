import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {ResourcesMenu as ResourcesMenuComponent} from '#/main/core/tools/resources/components/menu'

const ResourcesMenu = connect(
  (state) => ({
    path: toolSelectors.path(state),
    canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
  })
)(ResourcesMenuComponent)

export {
  ResourcesMenu
}
