import {ResourcesTool} from '#/main/core/tools/resources/components/tool'
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
    context: initialData.context,
    resourceManager: {
      initialized: true,
      root: initialData.root,
      current: initialData.root || null,
      directories: initialData.root ? [initialData.root] : []
    }
  })
})
