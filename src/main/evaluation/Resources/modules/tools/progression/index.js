import {reducer} from '#/main/evaluation/tools/progression/store'
import {ProgressionTool} from '#/main/evaluation/tools/progression/components/tool'
import {ProgressionMenu} from '#/main/evaluation/tools/progression/components/menu'

/**
 * Progression tool application.
 */
export default {
  component: ProgressionTool,
  menu: ProgressionMenu,
  store: reducer
}
