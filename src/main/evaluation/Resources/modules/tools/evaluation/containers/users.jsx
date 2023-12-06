import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {EvaluationUsers as EvaluationUsersComponent} from '#/main/evaluation/tools/evaluation/components/users'
import {actions} from '#/main/evaluation/tools/evaluation/store'

const EvaluationUsers = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextId: toolSelectors.contextId(state)
  }),
  (dispatch) => ({
    deleteEvaluation(workspaceId, userId) {
      dispatch(actions.deleteUserProgression(workspaceId, userId))
    },
    downloadParticipationCertificates(evaluations) {
      dispatch(actions.downloadParticipationCertificates(evaluations))
    },
    downloadSuccessCertificates(evaluations) {
      dispatch(actions.downloadSuccessCertificates(evaluations))
    }
  })
)(EvaluationUsersComponent)

export {
  EvaluationUsers
}
