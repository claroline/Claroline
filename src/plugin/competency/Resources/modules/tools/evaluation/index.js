import {CompetencyTool} from '#/plugin/competency/tools/evaluation/containers/tool'
import {constants as toolConst} from '#/main/core/tool/constants'

export default {
  name: 'competencies',
  component: CompetencyTool,
  styles: ['claroline-distribution-plugin-competency-competency'],
  displayed: (contextType, permissions = {}) => toolConst.TOOL_DESKTOP === contextType && permissions.edit
}