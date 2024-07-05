import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {ContextEditor as ContextEditorComponent} from '#/main/app/context/editor/components/main'
import {selectors as baseSelectors} from '#/main/app/context/store'
import {actions, reducer, selectors} from '#/main/app/context/editor/store'
import {actions as formActions} from '#/main/app/content/form/store'

const ContextEditor = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: baseSelectors.path(state),
      contextData: baseSelectors.data(state),
      contextName: baseSelectors.type(state),
      contextId: baseSelectors.id(state),
      tools: baseSelectors.tools(state),
      formData: selectors.context(state)
    }),
    (dispatch) => ({
      openEditor(contextData, tools) {
        dispatch(formActions.reset(selectors.FORM_NAME, {data: contextData, tools: tools}, false))
      },
      getAvailableTools(contextName, contextId) {
        return dispatch(actions.fetchAvailableTools(contextName, contextId))
      },
      refresh(updatedData) {
        dispatch(actions.refresh(updatedData))
      }
    })
  )(ContextEditorComponent)
)

export {
  ContextEditor
}
