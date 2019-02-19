import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {DragNDropContext} from '#/main/app/overlay/dnd'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {QuizResource as QuizResourceComponent} from '#/plugin/exo/resources/quiz/components/resource'

import {reducer} from '#/plugin/exo/quiz/reducer'
import {select} from '#/plugin/exo/quiz/selectors'

import {actions as correctionActions} from '#/plugin/exo/quiz/correction/actions'
import {actions as papersActions} from '#/plugin/exo/quiz/papers/actions'
import {actions as playerActions} from '#/plugin/exo/quiz/player/actions'
import {actions as statisticsActions} from '#/plugin/exo/quiz/statistics/actions'

const QuizResource = DragNDropContext(
  withRouter(
    withReducer(select.STORE_NAME, reducer)(
      connect(
        (state) => ({
          quizId: select.id(state),
          editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
          hasOverview: select.hasOverview(state),
          papersAdmin: select.papersAdmin(state),
          docimologyAdmin: select.docimologyAdmin(state),
          showStatistics: select.parameters(state).showStatistics || false,
          registeredUser: select.registered()
        }),
        (dispatch) => ({
          testMode(testMode) {
            dispatch(playerActions.setTestMode(testMode))
          },
          statistics() {
            dispatch(statisticsActions.displayStatistics())
          },
          correction(questionId = null) {
            if (!questionId) {
              dispatch(correctionActions.displayQuestions())
            } else {
              dispatch(correctionActions.displayQuestionAnswers(questionId))
            }
          },

          // TODO : move in papers app
          loadCurrentPaper(quizId, paperId) {
            dispatch(papersActions.loadCurrentPaper(quizId, paperId))
          },
          resetCurrentPaper() {
            dispatch(papersActions.setCurrentPaper(null))
          }
        })
      )(QuizResourceComponent)
    )
  )
)

export {
  QuizResource
}
