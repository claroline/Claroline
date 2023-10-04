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
    downloadParticipationCertificate(evaluation) {
      dispatch(actions.downloadParticipationCertificate(evaluation))
    },
    downloadSuccessCertificate(evaluation) {
      dispatch(actions.downloadSuccessCertificate(evaluation))
    },
    deleteEvaluation(workspaceId, userId) {
      dispatch(actions.deleteUserProgression(workspaceId, userId))
    }
  })
)(EvaluationUsersComponent)

export {
  EvaluationUsers
}
