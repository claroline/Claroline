import {reducer} from '#/plugin/competency/administration/competency/store'
import {CompetencyTool} from '#/plugin/competency/administration/competency/components/tool'

export const App = () => ({
  component: CompetencyTool,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData)
})