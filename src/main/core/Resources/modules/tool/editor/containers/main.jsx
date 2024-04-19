import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ToolEditor as ToolEditorComponent} from '#/main/core/tool/editor/components/main'
import {actions, reducer, selectors} from '#/main/core/tool/editor/store'

const ToolEditor = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: toolSelectors.loaded(state),
      path: toolSelectors.path(state),
      name: toolSelectors.name(state),
      tool: toolSelectors.toolData(state),
      contextType: toolSelectors.contextType(state),
      contextId: toolSelectors.contextId(state),
    }),
    (dispatch) => ({
      load(toolParameters) {
        dispatch(formActions.load(selectors.STORE_NAME, {data: toolParameters}))
      },
      refresh(toolName, updatedData, contextType) {
        dispatch(actions.refresh(toolName, updatedData, contextType))
      }
    })
  )(ToolEditorComponent)
)

export {
  ToolEditor
}
