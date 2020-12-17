import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as resourcesToolSelectors} from '#/main/core/tools/resources/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {PlayerMain as PlayerMainComponent} from '#/main/core/resources/directory/player/components/main'
import {selectors} from '#/main/core/resources/directory/player/store'
import {selectors as directorySelectors} from '#/main/core/resources/directory/store'

const PlayerMain = connect(
  (state) => ({
    path: resourceSelectors.basePath(state), // the base path without current resource id
    currentUser: securitySelectors.currentUser(state),
    embedded: resourceSelectors.embedded(state),
    rootNode: resourcesToolSelectors.root(state),
    currentNode: resourceSelectors.resourceNode(state),
    listName: selectors.LIST_NAME,
    listConfiguration: selectors.listConfiguration(state),
    storageLock: directorySelectors.storageLock(state)
  }),
  (dispatch) => ({
    updateNodes() {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
    },

    deleteNodes() {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
    }
  })
)(PlayerMainComponent)

export {
  PlayerMain
}
