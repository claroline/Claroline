import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {toKey} from '#/main/core/scaffolding/text'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {EditorMain as EditorMainComponent} from '#/plugin/path/resources/path/editor/components/main'
import {actions, selectors} from '#/plugin/path/resources/path/editor/store'
import {actions as pathActions} from '#/plugin/path/resources/path/store'
import {flattenSteps} from '#/plugin/path/resources/path/utils'
import {getStepTitle, getStepSlug} from '#/plugin/path/resources/path/editor/utils'

const EditorMain = withRouter(
  connect(
    (state) => ({
      basePath: resourceSelectors.path(state),
      path: selectors.path(state),
      steps: flattenSteps(selectors.steps(state)),
      resourceParent: resourceSelectors.parent(state),
      workspace: resourceSelectors.workspace(state)
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
      },

      computeResourceDuration(resourceId) {
        dispatch(pathActions.computeResourceDuration(resourceId))
      }
    })
  )(EditorMainComponent)
)

export {
  EditorMain
}
