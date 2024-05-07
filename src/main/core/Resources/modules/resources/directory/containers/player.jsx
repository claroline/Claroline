import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as resourcesToolSelectors} from '#/main/core/tools/resources/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {DirectoryPlayer as DirectoryPlayerComponent} from '#/main/core/resources/directory/components/player'
import {selectors} from '#/main/core/resources/directory/store'
import {actions} from '#/main/core/resource/modals/files/store'

const DirectoryPlayer = connect(
  (state) => ({
    path: resourceSelectors.basePath(state), // the base path without current resource id
    currentUser: securitySelectors.currentUser(state),
    embedded: resourceSelectors.embedded(state),
    rootNode: resourcesToolSelectors.root(state),
    currentNode: resourceSelectors.resourceNode(state),
    listName: selectors.LIST_NAME,
    listConfiguration: selectors.listConfiguration(state),
    storageLock: selectors.storageLock(state)
  }),
  (dispatch) => ({
    createFiles(parent, files) {
      return dispatch(actions.createFiles(parent, files))
    },
    updateNodes() {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
    },

    deleteNodes() {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
    }
  })
)(DirectoryPlayerComponent)

export {
  DirectoryPlayer
}
