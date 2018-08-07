import {reducer} from '#/plugin/team/tools/team/store'
import {TeamTool} from '#/plugin/team/tools/team/components/tool'

export const App = () => ({
  component: TeamTool,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData)
})