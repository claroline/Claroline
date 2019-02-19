import {connect} from 'react-redux'

import {QuizCreation as QuizCreationComponent} from '#/plugin/exo/resources/quiz/components/creation'
import {actions} from '#/main/core/resource/modals/creation/store'

const QuizCreation = connect(
  null,
  (dispatch) => ({
    changeType(newType) {
      dispatch(actions.updateResource('parameters.type', newType))
      // TODO : add presets
    }
  })
)(QuizCreationComponent)

export {
  QuizCreation
}
