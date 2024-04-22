import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as workspaceSelectors} from '#/main/app/contexts/workspace/store'

import {EvaluationEditor as EvaluationEditorComponent} from '#/main/evaluation/tools/evaluation/components/editor'
import {actions} from '#/main/evaluation/tools/evaluation/store'

const EvaluationEditor = connect(
  (state) => ({
    contextId: toolSelectors.contextId(state),
    workspaceRoot: workspaceSelectors.root(state)
  }),
  (dispatch) => ({
    addRequiredResources(contextId, resources) {
      dispatch(actions.addRequiredResources(contextId, resources))
    }
  })
)(EvaluationEditorComponent)

export {
  EvaluationEditor
}
