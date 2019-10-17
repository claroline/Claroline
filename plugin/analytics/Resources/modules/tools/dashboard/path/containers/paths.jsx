import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {actions, selectors} from '#/plugin/analytics/tools/dashboard/path/store'
import {Paths as PathsComponent} from '#/plugin/analytics/tools/dashboard/path/components/paths'
import {MODAL_STEP_DETAILS} from '#/plugin/analytics/tools/dashboard/path/modals/step-details'

const Paths = connect(
  (state) => ({
    workspaceId: toolSelectors.contextData(state).uuid,
    tracking: selectors.tracking(state)
  }),
  (dispatch) => ({
    fetchPathsData(workspaceId) {
      dispatch(actions.fetchPathsData(workspaceId))
    },
    invalidateEvaluations() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.evaluations'))
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

export {
  Paths
}
