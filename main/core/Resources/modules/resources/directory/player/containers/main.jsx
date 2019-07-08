import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {PlayerMain as PlayerMainComponent} from '#/main/core/resources/directory/player/components/main'
import {selectors} from '#/main/core/resources/directory/player/store'

const PlayerMain = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    path: resourceSelectors.basePath(state), // the base path without current resource id
    id: resourceSelectors.id(state),
    listName: selectors.LIST_NAME,
    listConfiguration: selectors.listConfiguration(state)
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
