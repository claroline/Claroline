import {connect} from 'react-redux'

import {selectors as toolSelectors} from  '#/main/core/tool/store'

import {actions} from '#/plugin/analytics/tools/dashboard/progression/store'
import {ProgressionParameters as ProgressionParametersComponent} from '#/plugin/analytics/tools/dashboard/progression/components/parameters'

const ProgressionParameters = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    addRoles(workspace, roles) {
      dispatch(actions.createRequirementsForRoles(workspace, roles))
    },
    addUsers(workspace, users) {
      dispatch(actions.createRequirementsForUsers(workspace, users))
    }
  })
)(ProgressionParametersComponent)

export {
  ProgressionParameters
}
