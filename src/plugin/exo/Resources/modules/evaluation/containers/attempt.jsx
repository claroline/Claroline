import {connect} from 'react-redux'

import {QuizEvaluationAttempt as QuizEvaluationAttemptComponent} from '#/plugin/exo/evaluation/components/attempt'
import {actions, reducer, selectors} from '#/plugin/exo/evaluation/store'
import {withReducer} from '#/main/app/store/reducer'
import {showScore} from '#/plugin/exo/resources/quiz/papers/restrictions'

const QuizEvaluationAttempt = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => {
      const admin = false // FIXME
      const paper = selectors.currentPaper(state)

      return ({
        quizId: selectors.quizId(state),
        steps: selectors.currentSteps(state),
        answers: selectors.answers(state),
        showScore: paper ? showScore(paper, admin) : false,
        showTitles: selectors.showTitles(state),
        showQuestionTitles: selectors.showQuestionTitles(state),
        numberingType: selectors.currentNumbering(state),
        questionNumberingType: selectors.currentQuestionNumbering(state),
        showExpectedAnswers: selectors.showExpectedAnswers(state),
        showStatistics: selectors.showStatistics(state),
        stats: selectors.stats(state)
      })
    },
    (dispatch) => ({
      loadCurrentPaper(attemptId) {
        return dispatch(actions.loadCurrentPaper(attemptId))
      },
      statistics(resourceId) {
        dispatch(actions.fetchStatistics(resourceId))
      }
    })
  )(QuizEvaluationAttemptComponent)
)

export {
  QuizEvaluationAttempt
}
