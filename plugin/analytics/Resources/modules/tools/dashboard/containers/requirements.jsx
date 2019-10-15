import {connect} from 'react-redux'

import {selectors as toolSelectors} from  '#/main/core/tool/store'

import {actions} from '#/plugin/analytics/tools/dashboard/store'
import {Requirements as RequirementsComponent} from '#/plugin/analytics/tools/dashboard/components/requirements'

const Requirements = connect(
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
)(RequirementsComponent)

export {
  Requirements
}
