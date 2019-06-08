import {DashboardTool} from '#/main/core/administration/dashboard/containers/tool'
import {reducer} from '#/main/core/administration/dashboard/store/reducer'

/**
 * Dashboard admin tool application.
 *
 * @constructor
 */
export const App = () => ({
  component: DashboardTool,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData, {
    tool: {
      name: 'platform_dashboard',
      currentContext: initialData.currentContext
    }
  })
})