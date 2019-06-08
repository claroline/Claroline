import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {select as listSelect} from '#/main/app/content/list/store'

import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {DashboardTool as DashboardToolComponent} from '#/main/core/tools/dashboard/components/tool'

const DashboardTool = withRouter(connect(
  state => ({
    workspaceId: state.workspace.id,
    connectionsQuery: listSelect.queryString(listSelect.list(state, 'connections.list')),
    logsQuery: listSelect.queryString(listSelect.list(state, 'logs')),
    usersQuery: listSelect.queryString(listSelect.list(state, 'userActions'))
  }),
  dispatch => ({
    openLog(id, workspaceId) {
      dispatch(logActions.openLog('apiv2_workspace_tool_logs_get', {id, workspaceId}))
    }
  })
)(DashboardToolComponent))

export {
  DashboardTool
}
