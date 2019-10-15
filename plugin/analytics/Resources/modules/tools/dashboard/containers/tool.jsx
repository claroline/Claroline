import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {select as listSelect} from '#/main/app/content/list/store'

import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {actions, selectors} from '#/plugin/analytics/tools/dashboard/store'
import {DashboardTool as DashboardToolComponent} from '#/plugin/analytics/tools/dashboard/components/tool'

const DashboardTool = withRouter(connect(
  state => ({
    workspaceId: toolSelectors.contextData(state).id,
    connectionsQuery: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.connections.list')),
    logsQuery: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.logs')),
    usersQuery: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.userActions'))
  }),
  dispatch => ({
    openLog(id, workspaceId) {
      dispatch(logActions.openLog('apiv2_workspace_tool_logs_get', {id, workspaceId}))
    },
    openRequirements(id) {
      dispatch(actions.openRequirements(id))
    },
    resetRequirements() {
      dispatch(actions.loadRequirements(null))
    }
  })
)(DashboardToolComponent))

export {
  DashboardTool
}
