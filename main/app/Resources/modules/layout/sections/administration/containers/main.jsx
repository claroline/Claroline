import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {constants as toolConst} from '#/main/core/tool/constants'
import {actions as toolActions} from '#/main/core/tool/store'

import {AdministrationMain as AdministrationMainComponent} from '#/main/app/layout/sections/administration/components/main'
import {actions, reducer, selectors} from '#/main/app/layout/sections/administration/store'

const AdministrationMain = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        loaded: selectors.loaded(state),
        defaultOpening: selectors.defaultOpening(state),
        tools: selectors.tools(state)
      }),
      (dispatch) => ({
        open() {
          dispatch(actions.open())
        },
        openTool(toolName) {
          dispatch(toolActions.open(toolName, {
            type: toolConst.TOOL_ADMINISTRATION,
            url: ['claro_admin_open_tool', {toolName: toolName}],
            data: {}
          }, '/admin'))
        }
      })
    )(AdministrationMainComponent)
  )
)

export {
  AdministrationMain
}
