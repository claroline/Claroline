import {reducer} from '#/main/core/administration/dashboard/store/reducer'
import {DashboardTool} from '#/main/core/administration/dashboard/containers/tool'
import {DashboardMenu} from '#/main/core/administration/dashboard/components/menu'

/**
 * Dashboard admin tool application.
 */
export default {
  component: DashboardTool,
  menu: DashboardMenu,
  store: reducer
}