import {reducer} from '#/plugin/analytics/administration/dashboard/store'
import {DashboardTool} from '#/plugin/analytics/administration/dashboard/containers/tool'
import {DashboardMenu} from '#/plugin/analytics/administration/dashboard/components/menu'

/**
 * Dashboard admin tool application.
 */
export default {
  component: DashboardTool,
  menu: DashboardMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-analytics-dashboard-tool']
}
