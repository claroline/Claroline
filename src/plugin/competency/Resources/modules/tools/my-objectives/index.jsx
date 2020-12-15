import {reducer} from '#/plugin/competency/tools/my-objectives/store'
import {MyObjectivesTool} from '#/plugin/competency/tools/my-objectives/components/tool'

export default {
  component: MyObjectivesTool,
  store: reducer,
  styles: ['claroline-distribution-plugin-competency-my-objectives']
}