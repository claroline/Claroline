import {reducer} from '#/main/evaluation/tools/evaluation/store'
import {EvaluationTool} from '#/main/evaluation/tools/evaluation/containers/tool'
import {EvaluationMenu} from '#/main/evaluation/tools/evaluation/containers/menu'

/**
 * Evaluation tool application.
 */
export default {
  component: EvaluationTool,
  menu: EvaluationMenu,
  store: reducer
}
