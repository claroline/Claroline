import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolMain as ToolMainComponent} from '#/main/core/tool/components/main'
import {actions, reducer, selectors} from '#/main/core/tool/store'

const ToolMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: selectors.path(state),
      toolName: selectors.name(state),
      toolContext: selectors.context(state),
      loaded: selectors.loaded(state),
      accessDenied: selectors.accessDenied(state),
      notFound: selectors.notFound(state)
    }),
    (dispatch) => ({
      open(toolName, context) {
        return dispatch(actions.fetch(toolName, context))
      },
      close(toolName, context) {
        if (toolName) {
          dispatch(actions.closeTool(toolName, context))
        }
      }
    })
  )(ToolMainComponent)
)

export {
  ToolMain
}
