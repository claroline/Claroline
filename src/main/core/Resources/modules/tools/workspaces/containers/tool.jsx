import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {reducer, selectors} from '#/main/core/tools/workspaces/store'
import {WorkspacesTool as WorkspacesToolComponent} from '#/main/core/tools/workspaces/components/tool'
import {withReducer} from '#/main/app/store/reducer'

const WorkspacesTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      canCreate: selectors.creatable(state),
      contextType: toolSelectors.contextType(state),
    }),
    (dispatch) => ({
      invalidateList(listName) {
        dispatch(listActions.invalidateData(listName))
      }
    })
  )(WorkspacesToolComponent)
)

export {
  WorkspacesTool
}
