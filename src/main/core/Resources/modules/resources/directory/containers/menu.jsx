import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourcesToolSelectors} from '#/main/core/tools/resources/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {DirectoryMenu as DirectoryMenuComponent} from '#/main/core/resources/directory/components/menu'
import {actions, selectors} from '#/main/core/resources/directory/store'

const DirectoryMenu = withRouter(
  connect(
    (state) => ({
      basePath: resourceSelectors.basePath(state),
      rootNode: resourcesToolSelectors.root(state),
      currentNode: resourceSelectors.resourceNode(state),
      directories: selectors.directories(state)
    }),
    (dispatch) => ({
      fetchDirectories(parentId = null) {
        dispatch(actions.fetchDirectories(parentId))
      },
      toggleDirectoryOpen(directoryId, opened) {
        dispatch(actions.toggleDirectoryOpen(directoryId, opened))
      }
    })
  )(DirectoryMenuComponent)
)

export {
  DirectoryMenu
}
