import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {actions as logActions} from  '#/main/core/layout/logs/actions'

import {DashboardMain as DashboardMainComponent} from '#/plugin/path/resources/path/dashboard/components/main'

const DashboardMain = connect(
  (state) => ({
    resourceId: resourceSelectors.resourceNode(state).autoId
  }),
  dispatch => ({
    openLog(id, resourceId) {
      dispatch(logActions.openLog('apiv2_resource_logs_get', {id, resourceId}))
    }
  })
)(DashboardMainComponent)

export {
  DashboardMain
}
