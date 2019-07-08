import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {DragNDropContext} from '#/main/app/overlays/dnd'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {QuizResource as QuizResourceComponent} from '#/plugin/exo/resources/quiz/components/resource'

import {reducer, selectors} from '#/plugin/exo/resources/quiz/store'

import {actions as playerActions} from '#/plugin/exo/quiz/player/actions'
import {actions as statisticsActions} from '#/plugin/exo/quiz/statistics/store'

const QuizResource = DragNDropContext(
  withRouter(
    withReducer(selectors.STORE_NAME, reducer)(
      connect(
        (state) => ({
          quizId: selectors.id(state),
          empty: selectors.empty(state),
          editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
          papersAdmin: hasPermission('manage_papers', resourceSelectors.resourceNode(state)),
          docimologyAdmin: hasPermission('view_docimology', resourceSelectors.resourceNode(state)),
          hasOverview: selectors.hasOverview(state),
          showStatistics: selectors.showStatistics(state),
          registeredUser: securitySelectors.isAuthenticated(state)
        }),
        (dispatch) => ({
          testMode(testMode) {
            dispatch(playerActions.setTestMode(testMode))
          },
          statistics(quizId) {
            dispatch(statisticsActions.fetchStatistics(quizId))
          }
        })
      )(QuizResourceComponent)
    )
  )
)

export {
  QuizResource
}
