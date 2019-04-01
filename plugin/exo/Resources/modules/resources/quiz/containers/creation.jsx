import {connect} from 'react-redux'

import {QuizCreation as QuizCreationComponent} from '#/plugin/exo/resources/quiz/components/creation'
import {actions} from '#/main/core/resource/modals/creation/store'

const QuizCreation = connect(
  null,
  (dispatch) => ({
    changeType(newType) {
      dispatch(actions.updateResource(null, Object.assign({
        parameters: {type: newType.name}
      }, newType.defaultProps || {})))
    }
  })
)(QuizCreationComponent)

export {
  QuizCreation
}
