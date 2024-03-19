import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

import {DirectoryMenu as DirectoryMenuComponent} from '#/main/core/resources/directory/components/menu'

const DirectoryMenu = connect(
  (state) => ({
    basePath: toolSelectors.path(state),
    isRoot: resourceSelectors.isRoot(state),
    canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
  })
)(DirectoryMenuComponent)

export {
  DirectoryMenu
}
