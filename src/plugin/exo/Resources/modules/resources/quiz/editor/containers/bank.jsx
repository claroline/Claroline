import {connect} from 'react-redux'

import {QuizEditorBank as QuizEditorBankComponent} from '#/plugin/exo/resources/quiz/editor/components/bank'
import {actions} from '#/plugin/exo/resources/quiz/editor/store/actions'

const QuizEditorBank = connect(
  null,
  (dispatch) => ({
    shareQuestions(questions, users, adminRights) {
      dispatch(actions.shareQuestions(questions, users, adminRights))
    }
  })
)(QuizEditorBankComponent)

export {
  QuizEditorBank
}
