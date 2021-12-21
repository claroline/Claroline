import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as workspaceSelectors} from '#/main/core/workspace/store'

import {EvaluationParameters as EvaluationParametersComponent} from '#/main/evaluation/tools/evaluation/components/parameters'
import {actions} from '#/main/evaluation/tools/evaluation/store'

const EvaluationParameters = connect(
  (state) => ({
    contextId: toolSelectors.contextId(state),
    workspaceRoot: workspaceSelectors.root(state)
  }),
  (dispatch) => ({
    addRequiredResources(contextId, resources) {
      dispatch(actions.addRequiredResources(contextId, resources))
    }
  })
)(EvaluationParametersComponent)

export {
  EvaluationParameters
}
