import {connect} from 'react-redux'

import {QuizCreation as QuizCreationComponent} from '#/plugin/exo/resources/quiz/components/creation'
import {actions} from '#/main/core/resource/modals/creation/store'

import {setTypePresets} from '#/plugin/exo/resources/quiz/types'

const QuizCreation = connect(
  null,
  (dispatch) => ({
    changeType(quizType) {
      dispatch(actions.updateResource(null, setTypePresets(quizType, {})))
    }
  })
)(QuizCreationComponent)

export {
  QuizCreation
}
