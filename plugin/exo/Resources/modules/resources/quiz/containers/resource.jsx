import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {DragNDropContext} from '#/main/app/overlay/dnd'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {QuizResource as QuizResourceComponent} from '#/plugin/exo/resources/quiz/components/resource'
import {TYPE_QUIZ} from '#/plugin/exo/quiz/enums'

import {reducer} from '#/plugin/exo/quiz/reducer'
import {select} from '#/plugin/exo/quiz/selectors'

import {actions as correctionActions} from '#/plugin/exo/quiz/correction/actions'
import {actions as editorActions} from '#/plugin/exo/quiz/editor/actions'
import {actions as papersActions} from '#/plugin/exo/quiz/papers/actions'
import {actions as playerActions} from '#/plugin/exo/quiz/player/actions'
import {actions as statisticsActions} from '#/plugin/exo/quiz/statistics/actions'

const QuizResource = DragNDropContext(
  withReducer(select.STORE_NAME, reducer)(
    connect(
      (state) => ({
        quizId: select.id(state),
        resourceNodeId: resourceSelectors.resourceNode(state).id,
        editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
        hasPapers: select.hasPapers(state),
        hasOverview: select.hasOverview(state),
        papersAdmin: select.papersAdmin(state),
        docimologyAdmin: select.docimologyAdmin(state),
        registeredUser: select.registered()
      }),
      (dispatch) => ({
        edit(quizId) {
          dispatch(editorActions.selectObject(quizId, TYPE_QUIZ))
        },
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
        loadCurrentPaper(paperId) {
          dispatch(papersActions.loadCurrentPaper(paperId))
        },
        resetCurrentPaper() {
          dispatch(papersActions.setCurrentPaper(null))
        }
      })
    )(QuizResourceComponent)
  )
)

export {
  QuizResource
}
