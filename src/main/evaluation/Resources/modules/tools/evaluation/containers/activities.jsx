import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as workspaceSelectors} from '#/main/app/contexts/workspace/store'

import {EvaluationActivities as EvaluationActivitiesComponent} from '#/main/evaluation/tools/evaluation/components/activities'
import {actions} from '#/main/evaluation/tools/evaluation/store'

const EvaluationActivities = connect(
  (state) => ({
    contextId: toolSelectors.contextId(state)
  }),
  (dispatch) => ({
    addRequiredResources(contextId, resources) {
      dispatch(actions.addRequiredResources(contextId, resources))
    }
  })
)(EvaluationActivitiesComponent)

export {
  EvaluationActivities
}
