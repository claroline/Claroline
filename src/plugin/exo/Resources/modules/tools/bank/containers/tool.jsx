import {connect} from 'react-redux'

import {BankTool as BankToolComponent} from '#/plugin/exo/tools/bank/components/tool'
import {actions} from '#/plugin/exo/tools/bank/store'

const BankTool = connect(
  null,
  (dispatch) => ({
    removeQuestions(questions) {
      dispatch(actions.removeQuestions(questions))
    },

    duplicateQuestions(questions) {
      dispatch(actions.duplicateQuestions(questions))
    },

    shareQuestions(questions, users, adminRights) {
      dispatch(actions.shareQuestions(questions, users, adminRights))
    }
  })
)(BankToolComponent)

export {
  BankTool
}
