import {reducer} from '#/plugin/analytics/tools/dashboard/store'
import {DashboardTool} from '#/plugin/analytics/tools/dashboard/containers/tool'
import {DashboardMenu} from '#/plugin/analytics/tools/dashboard/containers/menu'

/**
 * Dashboard tool application.
 */
export default {
  component: DashboardTool,
  menu: DashboardMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-analytics-dashboard-tool']
}
