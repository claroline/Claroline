import {connect} from 'react-redux'
import {hasPermission} from '#/main/app/security'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ResourcesRoot as ResourcesRootComponent} from '#/main/core/tools/resources/components/root'
import {selectors} from '#/main/core/tools/resources/store'

const ResourcesRoot = connect(
  (state) => ({
    path: toolSelectors.path(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    listName: selectors.LIST_ROOT_NAME
  })
)(ResourcesRootComponent)

export {
  ResourcesRoot
}
