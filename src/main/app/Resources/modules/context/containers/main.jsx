import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {actions as toolActions} from '#/main/core/tool/store'

import {ContextMain as ContextMainComponent} from '#/main/app/context/components/main'
import {actions, reducer, selectors} from '#/main/app/context/store'

const ContextMain = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        path: selectors.path(state),
        loaded: selectors.loaded(state),
        notFound: selectors.notFound(state),
        accessErrors: selectors.accessErrors(state),
        defaultOpening: selectors.defaultOpening(state),
        tools: selectors.tools(state)
      }),
      (dispatch) => ({
        open(contextType, contextId) {
          return dispatch(actions.open(contextType, contextId))
        },
        openTool(toolName) {
          dispatch(toolActions.open(toolName))
        }
      })
    )(ContextMainComponent)
  )
)

export {
  ContextMain
}
