import {connect} from 'react-redux'

import {selectors as resourceSelectors} from  '#/main/core/resource/store'
import {selectors as toolSelectors} from  '#/main/core/tool/store'

import {actions} from '#/plugin/analytics/analytics/resource/requirements/store'
import {Requirements as RequirementsComponent} from '#/plugin/analytics/analytics/resource/requirements/components/requirements'

const Requirements = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    resourceId: resourceSelectors.resourceNode(state).id,
    workspace: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    createRequirements(resourceId, objects, type) {
      dispatch(actions.createRequirements(resourceId, objects, type))
    }
  })
)(RequirementsComponent)

export {
  Requirements
}
