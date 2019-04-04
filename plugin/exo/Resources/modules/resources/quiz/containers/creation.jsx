import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {QuizCreation as QuizCreationComponent} from '#/plugin/exo/resources/quiz/components/creation'
import {actions} from '#/main/core/resource/modals/creation/store'
import {Quiz as QuizTypes} from '#/plugin/exo/resources/quiz/prop-types'
import {setTypePresets} from '#/plugin/exo/resources/quiz/types'

const QuizCreation = connect(
  null,
  (dispatch) => ({
    changeType(quizType) {
      dispatch(actions.updateResource(null, setTypePresets(quizType, merge({}, QuizTypes.defaultProps))))
    }
  })
)(QuizCreationComponent)

export {
  QuizCreation
}
