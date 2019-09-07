import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as explorerActions,
  selectors as explorerSelectors
} from '#/main/core/modals/resources/store'

import {ResourcesModal as ResourcesModalComponent} from '#/main/core/modals/resources/components/modal'
import {reducer, selectors} from '#/main/core/modals/resources/store'

const ResourcesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentDirectory: explorerSelectors.currentNode(state),
      selected: explorerSelectors.selectedFull(state)
    }),
    (dispatch) => ({
      initialize(root, current, filters) {
        dispatch(explorerActions.initialize(root, current, filters))
      }
    })
  )(ResourcesModalComponent)
)

export {
  ResourcesModal
}
