import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {ContextEditor as ContextEditorComponent} from '#/main/app/context/editor/components/main'
import {selectors as baseSelectors} from '#/main/app/context/store'
import {actions, reducer, selectors} from '#/main/app/context/editor/store'

const ContextEditor = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      contextName: baseSelectors.type(state),
      contextId: baseSelectors.id(state),
      tools: baseSelectors.tools(state),
      availableTools: selectors.availableTools(state)
    }),
    (dispatch) => ({
      getAvailableTools(contextName, contextId) {
        return dispatch(actions.fetchAvailableTools(contextName, contextId))
      }
    })
  )(ContextEditorComponent)
)

export {
  ContextEditor
}
