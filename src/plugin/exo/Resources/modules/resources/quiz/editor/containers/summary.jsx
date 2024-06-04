import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as editorSelectors} from '#/main/core/resource/editor'
import {QuizEditorSummary as QuizEditorSummaryComponent} from '#/plugin/exo/resources/quiz/editor/components/summary'
import {selectors} from '#/plugin/exo/resources/quiz/editor/store'
import {addStep} from '#/plugin/exo/resources/quiz/editor/utils'

const QuizEditorSummary = withRouter(
  connect(
    (state) => ({
      path: editorSelectors.path(state),
      steps: selectors.steps(state),
      numberingType: selectors.numberingType(state),
      questionNumberingType: selectors.questionNumberingType(state),
      errors: formSelectors.errors(formSelectors.form(state, editorSelectors.STORE_NAME))
    }),
    (dispatch) => ({
      addStep(steps = []) {
        const updatedSteps = addStep(steps)
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', updatedSteps))

        // return slug for redirection
        return updatedSteps[updatedSteps.length - 1].slug
      }
    })
  )(QuizEditorSummaryComponent)
)

export {
  QuizEditorSummary
}
