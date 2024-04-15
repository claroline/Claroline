import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {selectors, reducer} from '#/main/core/resources/directory/store'
import {DirectoryResource as DirectoryResourceComponent} from '#/main/core/resources/directory/components/resource'
import {selectors as toolSelectors} from '#/main/core/tool'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

const DirectoryResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      storageLock: selectors.storageLock(state),
      basePath: toolSelectors.path(state),
      isRoot: resourceSelectors.isRoot(state),
      canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
    })
  )(DirectoryResourceComponent)
)

export {
  DirectoryResource
}