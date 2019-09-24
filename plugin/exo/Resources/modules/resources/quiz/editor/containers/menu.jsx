import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {EditorMenu as EditorMenuComponent} from '#/plugin/exo/resources/quiz/editor/components/menu'
import {actions, selectors} from '#/plugin/exo/resources/quiz/editor/store'
import {getStepSlug} from '#/plugin/exo/resources/quiz/editor/utils'

const EditorMenu = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      steps: selectors.steps(state),
      validating: formSelectors.validating(formSelectors.form(state, selectors.FORM_NAME)),
      errors: formSelectors.errors(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      addStep(steps = []) {
        // generate slug now to be able to redirect
        const title = trans('step', {number: steps.length + 1}, 'quiz')
        const slug = getStepSlug(steps, toKey(title))

        dispatch(actions.addStep({
          slug: slug
        }))

        // return slug for redirection
        return slug
      },
      removeStep(stepId) {
        dispatch(actions.removeStep(stepId))
      },
      copyStep(stepId, position) {
        dispatch(actions.copyStep(stepId, position))
      },
      moveStep(stepId, position) {
        dispatch(actions.moveStep(stepId, position))
      }
    })
  )(EditorMenuComponent)
)

export {
  EditorMenu
}
