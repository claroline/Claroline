import {BankTool} from '#/plugin/exo/tools/bank/containers/tool'
import {reducer} from '#/plugin/exo/tools/bank/store'

/**
 * BankTool application.
 * Manages all the quiz questions accessible by the current user.
 */
export default {
  component: BankTool,
  // menu: ResourcesMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-exo-question-bank-tool']
}