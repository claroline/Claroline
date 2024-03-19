import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationUser as EvaluationUserComponent} from '#/main/evaluation/tools/evaluation/components/user'
import {selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationUser = connect(
  (state) => ({
    contextPath: toolSelectors.basePath(state),
    loaded: selectors.currentLoaded(state),
    workspaceEvaluation: selectors.currentWorkspaceEvaluation(state),
    resourceEvaluations: selectors.currentResourceEvaluations(state)
  })
)(EvaluationUserComponent)

export {
  EvaluationUser
}
