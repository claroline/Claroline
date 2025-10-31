import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {actions as editorActions} from '#/main/core/resource/editor/store'

import {QuizEditorParameters as QuizEditorParametersComponent} from '#/plugin/exo/resources/quiz/editor/components/parameters'
import {selectors} from '#/plugin/exo/resources/quiz/editor/store'

const QuizEditorParameters = withRouter(
  connect(
    (state) => ({
      quizType: selectors.quizType(state),
      score: selectors.score(state),
      numberingType: selectors.numberingType(state),
      randomPick: selectors.randomPick(state),
      tags: selectors.tags(state),
      workspace: resourceSelectors.workspace(state),
      steps: selectors.steps(state),
      errors: formSelectors.errors(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      /**
       * Change a quiz data value.
       *
       * @param {string} prop  - the path of the prop to update
       * @param {*}      value - the new value to set
       */
      updateProp(prop, value) {
        dispatch(editorActions.updateResource(value, prop))
      },
      update(value) {
        dispatch(editorActions.updateResource(value))
      },
      setErrors(errors) {
        dispatch(formActions.setErrors(selectors.FORM_NAME, errors))
      }
    })
  )(QuizEditorParametersComponent)
)

export {
  QuizEditorParameters
}
