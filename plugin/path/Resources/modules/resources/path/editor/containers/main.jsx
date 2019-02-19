import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {EditorMain as EditorMainComponent} from '#/plugin/path/resources/path/editor/components/main'
import {actions, selectors} from '#/plugin/path/resources/path/editor/store'
import {actions as pathActions, selectors as pathSelectors} from '#/plugin/path/resources/path/store'
import {flattenSteps} from '#/plugin/path/resources/path/utils'

const EditorMain = withRouter(
  connect(
    (state) => ({
      summaryOpened: pathSelectors.summaryOpened(state),
      summaryPinned: pathSelectors.summaryPinned(state),

      path: selectors.path(state),
      steps: flattenSteps(selectors.steps(state)),
      resourceParent: resourceSelectors.parent(state),
      workspace: resourceSelectors.workspace(state)
    }),
    (dispatch, ownProps) => ({
      addStep(parentId = null) {
        // generate id now to be able to redirect to new step
        const stepId = makeId()

        dispatch(actions.addStep({id: stepId}, parentId))

        ownProps.history.push(`/edit/${stepId}`)
      },
      removeStep(stepId) {
        dispatch(actions.removeStep(stepId))

        if (`/edit/${stepId}` === ownProps.history.location.pathname) {
          ownProps.history.push('/edit')
        }
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
