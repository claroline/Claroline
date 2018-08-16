import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as explorerActions,
  selectors as explorerSelectors
} from '#/main/core/resource/explorer/store'

import {ExplorerModal as ExplorerModalComponent} from '#/main/core/resource/modals/explorer/components/modal'
import {reducer, selectors} from '#/main/core/resource/modals/explorer/store'

const ExplorerModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentDirectory: explorerSelectors.current(explorerSelectors.explorer(state, selectors.STORE_NAME)),
      selected: explorerSelectors.selectedFull(explorerSelectors.explorer(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      initialize(root, current, filters) {
        dispatch(explorerActions.initialize(selectors.STORE_NAME, root, current, filters))
      }
    })
  )(ExplorerModalComponent)
)

export {
  ExplorerModal
}
