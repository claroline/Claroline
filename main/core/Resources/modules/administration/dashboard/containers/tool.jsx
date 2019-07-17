import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {select as listSelect} from '#/main/app/content/list/store'

import {actions as logActions} from  '#/main/core/layout/logs/actions'
import {selectors} from '#/main/core/administration/dashboard/store/selectors'
import {DashboardTool as DashboardToolComponent} from '#/main/core/administration/dashboard/components/tool'

const DashboardTool = withRouter(connect(
  state => ({
    connectionsQuery: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.connections.list')),
    logsQuery: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.logs')),
    usersQuery: listSelect.queryString(listSelect.list(state, selectors.STORE_NAME + '.userActions'))
  }),
  dispatch => ({
    openLog(id) {
      dispatch(logActions.openLog('apiv2_admin_tool_logs_get', {id}))
    }
  })
)(DashboardToolComponent))

export {
  DashboardTool
}
