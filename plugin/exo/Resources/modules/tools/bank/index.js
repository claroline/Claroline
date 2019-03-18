import {BankTool} from '#/plugin/exo/tools/bank/containers/tool'
import {reducer} from '#/plugin/exo/tools/bank/store'

/**
 * BankTool application.
 * Manages all the quiz questions accessible by the current user.
 */
export const App = () => ({
  component: BankTool,
  store: reducer,
  initialData: initialData => ({
    tool: {
      name: 'ujm_questions',
      currentContext: initialData.currentContext
    }
  })
})
