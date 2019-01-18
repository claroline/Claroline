import {ResourcesTool} from '#/main/core/tools/resources/containers/tool'
import {reducer} from '#/main/core/tools/resources/store'

/**
 * Resources tool application.
 *
 * @constructor
 */
export const App = () => ({
  component: ResourcesTool,
  store: reducer,
  initialData: initialData => ({
    tool: {
      name: 'resource_manager',
      currentContext: initialData.currentContext
    },
    resourceManager: {
      root: initialData.root,
      directories: initialData.root ? [initialData.root] : []
    }
  })
})
