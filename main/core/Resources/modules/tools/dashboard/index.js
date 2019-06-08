import {DashboardTool} from '#/main/core/tools/dashboard/containers/tool'
import {reducer} from '#/main/core/tools/dashboard/store/reducer'

/**
 * Dashboard tool application.
 *
 * @constructor
 */
export const App = () => ({
  component: DashboardTool,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData, {
    tool: {
      name: 'dashboard',
      currentContext: initialData.currentContext
    }
  })
})

