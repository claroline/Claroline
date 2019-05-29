import {TransferTool} from '#/main/core/tools/transfer/containers/tool'
//import {reducer} from '#/main/core/tools/transfer/store'
import {reducer} from '#/main/core/tools/transfer/store/reducer'

/**
 * Resources tool application.
 *
 * @constructor
 */
export const App = () => ({
  component: TransferTool,
  store: reducer,
  initialData: initialData => {
    return {
      tool: {
        name: 'transfer'
      },
      explanation: initialData.explanation,
      currentContext: initialData.currentContext
    }}
})
