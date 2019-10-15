import {connect} from 'react-redux'

import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {selectors as workspaceSelectors} from '#/main/core/workspace/store/selectors'

import {actions, selectors} from '#/plugin/analytics/tools/dashboard/store'
import {RequirementsDetails as RequirementsDetailsComponent} from '#/plugin/analytics/tools/dashboard/components/requirements-details'

const RequirementsDetails = connect(
  (state) => ({
    workspace: toolSelectors.contextData(state),
    root: workspaceSelectors.root(state),
    requirements: selectors.currentRequirements(state)
  }),
  (dispatch) => ({
    addResources(requirements, resources) {
      dispatch(actions.addRequirementsResources(requirements, resources))
    },
    removeResources(requirements, resources) {
      dispatch(actions.removeRequirementsResources(requirements, resources))
    }
  })
)(RequirementsDetailsComponent)

export {
  RequirementsDetails
}
