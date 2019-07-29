import {reducer} from '#/main/core/tools/dashboard/store'
import {DashboardTool} from '#/main/core/tools/dashboard/containers/tool'
import {DashboardMenu} from '#/main/core/tools/dashboard/components/menu'

/**
 * Dashboard tool application.
 */
export default {
  component: DashboardTool,
  menu: DashboardMenu,
  store: reducer
}

