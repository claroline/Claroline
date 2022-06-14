import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationTool as EvaluationToolComponent} from '#/main/evaluation/tools/evaluation/components/tool'

const EvaluationTool = connect(
  (state) => ({
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canShowEvaluations: hasPermission('show_evaluations', toolSelectors.toolData(state)),
    contextId: toolSelectors.contextId(state),
    currentUserId: securitySelectors.currentUserId(state)
  })
)(EvaluationToolComponent)

export {
  EvaluationTool
}
