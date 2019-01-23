import {TrashTool} from '#/main/core/tools/trash/containers/tool'
import {reducer} from '#/main/core/tools/trash/store'

/**
 * Resources tool application.
 *
 * @constructor
 */
export const App = () => ({
  component: TrashTool,
  store: reducer,
  initialData: initialData => ({
    tool: {
      name: 'resources',
      currentContext: initialData.currentContext
    }
  })
})
