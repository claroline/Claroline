import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {QuizResource as QuizResourceComponent} from '#/plugin/exo/resources/quiz/components/resource'

import {reducer, selectors} from '#/plugin/exo/resources/quiz/store'

import {actions as playerActions} from '#/plugin/exo/resources/quiz/player/store/actions'

const QuizResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      quizId: selectors.id(state),
      empty: selectors.empty(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
      canFollow: hasPermission('follow', resourceSelectors.resourceNode(state)),
      hasOverview: selectors.hasOverview(state),
      registeredUser: securitySelectors.isAuthenticated(state)
    }),
    (dispatch) => ({
      testMode(testMode) {
        dispatch(playerActions.setTestMode(testMode))
      }
    })
  )(QuizResourceComponent)
)

export {
  QuizResource
}
