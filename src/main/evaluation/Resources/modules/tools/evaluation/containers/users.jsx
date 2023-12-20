import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationUsers as EvaluationUsersComponent} from '#/main/evaluation/tools/evaluation/components/users'
import {selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationUsers = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    contextId: toolSelectors.contextId(state)
  }),
  (dispatch) => ({
    invalidate() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.workspaceEvaluations'))
    }
  })
)(EvaluationUsersComponent)

export {
  EvaluationUsers
}
