import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolMain as ToolMainComponent} from '#/main/core/tool/components/main'
import {actions, reducer, selectors} from '#/main/core/tool/store'

const ToolMain = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        path: selectors.path(state),
        toolName: selectors.name(state),
        toolContext: selectors.context(state),
        loaded: selectors.loaded(state)
      }),
      (dispatch) => ({
        open(toolName, context) {
          return dispatch(actions.fetch(toolName, context))
        },
        close(toolName, context) {
          dispatch(actions.closeTool(toolName, context))
        }
      })
    )(ToolMainComponent)
  )
)

export {
  ToolMain
}
