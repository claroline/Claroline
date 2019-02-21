import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelect} from '#/main/core/resource/store'

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
      resourceParent: resourceSelect.parent(state),
      workspace: resourceSelect.workspace(state)
    }),
    (dispatch) => ({
      addStep(parentId = null) {
        dispatch(actions.addStep(parentId))
      },
      removeStep(stepId, history) {
        dispatch(actions.removeStep(stepId))

        if (`/edit/${stepId}` === history.location.pathname) {
          history.push('/edit')
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
