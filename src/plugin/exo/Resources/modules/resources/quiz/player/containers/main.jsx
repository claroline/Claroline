import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {QuizPlayer as QuizPlayerComponent} from '#/plugin/exo/resources/quiz/player/components/main'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {actions, selectors as select} from '#/plugin/exo/resources/quiz/player/store'
import {DragNDropContext} from '#/main/app/overlays/dnd'

const QuizPlayer = DragNDropContext(withRouter(connect(
  state => {
    const paper = select.paper(state)
    return {
      // general info
      path: resourceSelect.path(state),
      workspace: resourceSelect.workspace(state),
      resourceId: resourceSelect.id(state),
      quizId: select.quizId(state),

      // general attempt info
      testMode: select.testMode(state),
      paper: paper,
      progression: select.progressionDisplayed(state) ? {
        current: Object.values(select.answers(state)).filter(a => a.data && a.data.length > 0).length,
        total: select.countItems(state)
      } : undefined,

      // attempt parameters
      mandatoryQuestions: select.mandatoryQuestions(state),
      numbering: select.quizNumbering(state),
      questionNumbering: select.questionNumbering(state),
      showTitles: select.showTitles(state),
      showQuestionTitles: select.showQuestionTitles(state),
      isTimed: select.isTimed(state),
      duration: select.duration(state),
      answersEditable: select.answersEditable(state),
      showStatistics: select.showStatistics(state),
      showBack: select.showBack(state),
      showFeedback: select.showFeedback(state),
      showEndConfirm: select.showEndConfirm(state),
      feedbackEnabled: select.feedbackEnabled(state),

      // current step info
      number: select.currentStepNumber(state),
      step: select.currentStep(state),
      items: select.currentStepItems(state),
      answers: select.currentStepAnswers(state),
      currentStepSend: select.currentStepSend(state),

      next: select.next(state),
      previous: select.previous(state)
    }
  },
  dispatch => ({
    start() {
      return dispatch(actions.play())
    },
    updateAnswer(questionId, answerData) {
      dispatch(actions.updateAnswer(questionId, answerData))
    },
    navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback, confirm = false) {
      if (confirm) {
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          question: trans('validate_step_question', {}, 'quiz'),
          confirmAction: {
            type: CALLBACK_BUTTON,
            callback: () => dispatch(actions.navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback))
          }
        }))
      } else {
        dispatch(actions.navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback))
      }
    },
    submit(quizId, paperId, answers) {
      dispatch(actions.submit(quizId, paperId, answers))
    },
    finish(quizId, paper, pendingAnswers, showFeedback, showConfirm, navigate) {
      if (showConfirm) {
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          question: trans('finish_confirm_question', {}, 'quiz'),
          confirmAction: {
            type: CALLBACK_BUTTON,
            callback: () => dispatch(actions.finish(quizId, paper, pendingAnswers, showFeedback, navigate))
          }
        }))
      } else {
        dispatch(actions.finish(quizId, paper, pendingAnswers, showFeedback, navigate))
      }
    },
    showHint(quizId, paperId, questionId, hint) {
      dispatch(actions.showHint(quizId, paperId, questionId, hint))
    },
    showTimeOverMessage() {
      dispatch(modalActions.showModal(MODAL_ALERT, {
        title: trans('time_over', {}, 'quiz'),
        message: trans('time_over_message', {}, 'quiz'),
        type: 'info'
      }))
    }
  })
)(QuizPlayerComponent)))

export {
  QuizPlayer
}
