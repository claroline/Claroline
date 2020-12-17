import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/core/modals/workspaces/store'
import {WorkspacesModal as WorkspacesModalComponent} from '#/main/core/modals/workspaces/components/modal'

const WorkspacesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selected: listSelectors.selectedFull(listSelectors.list(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      reset() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(WorkspacesModalComponent)
)

export {
  WorkspacesModal
}
