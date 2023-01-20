import {connect} from 'react-redux'

import {EditorBank as EditorBankComponent} from '#/plugin/exo/resources/quiz/editor/components/bank'
import {actions} from '#/plugin/exo/resources/quiz/editor/store/actions'

const EditorBank = connect(
  null,
  (dispatch) => ({
    shareQuestions(questions, users, adminRights) {
      dispatch(actions.shareQuestions(questions, users, adminRights))
    }
  })
)(EditorBankComponent)

export {
  EditorBank
}
