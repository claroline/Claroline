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
  initialData: initialData => Object.assign({}, initialData, {
    current: initialData.root || null
  })
})
