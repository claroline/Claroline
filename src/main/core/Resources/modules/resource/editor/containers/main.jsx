import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourceEditor as ResourceEditorComponent} from '#/main/core/resource/editor/components/main'
import {reducer} from '#/main/core/resource/editor/store'

const ResourceEditor = withReducer(resourceSelectors.EDITOR_NAME, reducer)(
  connect(
    (state) => ({
      loaded: resourceSelectors.loaded(state),
      resourceNode: resourceSelectors.resourceNode(state)
    }),
    (dispatch) => ({
      load(resourceNode) {
        dispatch(formActions.load(resourceSelectors.EDITOR_NAME, {resourceNode: resourceNode}))
      },
      refresh(toolName, updatedData, contextType) {
        // TODO : implement
      }
    })
  )(ResourceEditorComponent)
)

export {
  ResourceEditor
}
