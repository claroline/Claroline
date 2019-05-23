import {connect} from 'react-redux'

import {CorrectionMain as CorrectionMainComponent} from '#/plugin/exo/resources/quiz/correction/components/main'

import {actions as correctionActions} from '#/plugin/exo/resources/quiz/correction/store'

const CorrectionMain = connect(
  null,
  (dispatch) => ({
    correction(questionId = null) {
      if (!questionId) {
        dispatch(correctionActions.displayQuestions())
      } else {
        dispatch(correctionActions.displayQuestionAnswers(questionId))
      }
    }
  })
)(CorrectionMainComponent)

export {
  CorrectionMain
}
