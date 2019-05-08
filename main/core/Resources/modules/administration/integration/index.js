import {IntegrationTool} from '#/main/core/administration/integration/components/tool'
import {reducer} from '#/main/core/administration/integration/store/reducer'
/**
 * Resources tool application.
 *
 * @constructor
 */
export const App = () => ({
  component: IntegrationTool,
  store: reducer
})
