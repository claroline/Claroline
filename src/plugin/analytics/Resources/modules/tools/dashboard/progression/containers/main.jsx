import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as toolSelectors} from  '#/main/core/tool/store'

import {ProgressionMain as ProgressionMainComponent} from '#/plugin/analytics/tools/dashboard/progression/components/main'
import {actions} from '#/plugin/analytics/tools/dashboard/progression/store'

const ProgressionMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspaceId: toolSelectors.contextId(state),
    canConfigure: hasPermission('edit', toolSelectors.toolData(state)),
    currentUserId: securitySelectors.currentUserId(state)
  }),
  (dispatch) => ({
    openRequirements(id) {
      dispatch(actions.openRequirements(id))
    },
    resetRequirements() {
      dispatch(actions.loadRequirements(null))
    }
  })
)(ProgressionMainComponent)

export {
  ProgressionMain
}
