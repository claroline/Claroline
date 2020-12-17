import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {toKey} from '#/main/core/scaffolding/text'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {EditorMenu as EditorMenuComponent} from '#/plugin/path/resources/path/editor/components/menu'
import {actions, selectors} from '#/plugin/path/resources/path/editor/store'
import {flattenSteps} from '#/plugin/path/resources/path/utils'
import {getStepTitle, getStepSlug} from '#/plugin/path/resources/path/editor/utils'

const EditorMenu = withRouter(
  connect(
    (state) => ({
      basePath: resourceSelectors.path(state),
      path: selectors.path(state),
      steps: flattenSteps(selectors.steps(state))
    }),
    (dispatch) => ({
      addStep(steps, parent = null) {
        // generate slug now to be able to redirect
        const title = getStepTitle(steps, parent)
        const slug = getStepSlug(steps, toKey(title))

        dispatch(actions.addStep({
          title: title,
          slug: slug
        }, parent ? parent.id : null))

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
