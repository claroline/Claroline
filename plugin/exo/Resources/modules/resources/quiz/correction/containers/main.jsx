import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {actions as correctionActions} from '#/plugin/exo/resources/quiz/correction/store'
import {CorrectionMain as CorrectionMainComponent} from '#/plugin/exo/resources/quiz/correction/components/main'

const CorrectionMain = connect(
  (state) => ({
    path: resourceSelectors.path(state)
  }),
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
