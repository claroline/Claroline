import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolMain as ToolMainComponent} from '#/main/core/tool/components/main'
import {actions, reducer, selectors} from '#/main/core/tool/store'

const ToolMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: selectors.path(state),
      toolName: selectors.name(state),
      contextType: selectors.contextType(state),
      contextId: selectors.contextId(state),
      loaded: selectors.loaded(state),
      accessDenied: selectors.accessDenied(state),
      notFound: selectors.notFound(state)
    }),
    (dispatch) => ({
      open(toolName, context, contextId) {
        return dispatch(actions.fetch(toolName, context, contextId))
      },
      close(toolName, context, contextId) {
        if (toolName) {
          dispatch(actions.closeTool(toolName, context, contextId))
        }
      }
    })
  )(ToolMainComponent)
)

export {
  ToolMain
}
