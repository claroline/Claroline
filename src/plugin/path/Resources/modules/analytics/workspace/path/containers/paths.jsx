import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as toolSelectors} from  '#/main/core/tool/store'

import {actions, reducer, selectors} from '#/plugin/path/analytics/workspace/path/store'
import {Paths as PathsComponent} from '#/plugin/path/analytics/workspace/path/components/paths'
import {MODAL_STEP_DETAILS} from '#/plugin/path/analytics/workspace/path/modals/step-details'

const Paths = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      workspaceId: toolSelectors.contextId(state),
      tracking: selectors.tracking(state)
    }),
    (dispatch) => ({
      fetchPathsData(workspaceId) {
        dispatch(actions.fetchPathsData(workspaceId))
      },
      showStepDetails(users) {
        dispatch(
          modalActions.showModal(MODAL_STEP_DETAILS, {
            users: users
          })
        )
      }
    })
  )(PathsComponent)
)

export {
  Paths
}
