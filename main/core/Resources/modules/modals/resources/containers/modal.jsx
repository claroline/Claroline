import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as explorerActions,
  selectors as explorerSelectors
} from '#/main/core/resource/explorer/store'

import {ResourcesModal as ResourcesModalComponent} from '#/main/core/modals/resources/components/modal'
import {reducer, selectors} from '#/main/core/modals/resources/store'

const ResourcesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentDirectory: explorerSelectors.currentNode(explorerSelectors.explorer(state, selectors.STORE_NAME)),
      selected: explorerSelectors.selectedFull(explorerSelectors.explorer(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      initialize(root, current, filters) {
        dispatch(explorerActions.initialize(selectors.STORE_NAME, root, current, filters))
      }
    })
  )(ResourcesModalComponent)
)

export {
  ResourcesModal
}
