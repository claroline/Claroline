import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolMenu as ToolMenuComponent} from '#/main/core/tool/components/menu'
import {actions, reducer, selectors} from '#/main/core/tool/store'

const ToolMenu = withRouter(
  /*withReducer(selectors.STORE_NAME, reducer)(*/
    connect(
      (state) => ({
        //contextType: selectors.contextType(state),
        name: selectors.name(state),
        path: selectors.path(state),
        toolData: selectors.toolData(state),
        loaded: selectors.loaded(state),
        notFound: selectors.notFound(state),
        currentContext: selectors.context(state)
      }),
      (dispatch) => ({
        reload() {
          dispatch(actions.setLoaded(false))
        }
      })
    )(ToolMenuComponent)
  /*)*/
)

export {
  ToolMenu
}
