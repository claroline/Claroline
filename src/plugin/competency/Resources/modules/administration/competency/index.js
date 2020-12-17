import {reducer} from '#/plugin/competency/administration/competency/store'
import {CompetencyTool} from '#/plugin/competency/administration/competency/containers/tool'
import {CompetencyMenu} from '#/plugin/competency/administration/competency/components/menu'

export default {
  component: CompetencyTool,
  menu: CompetencyMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-competency-competency']
}