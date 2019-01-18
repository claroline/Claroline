import {reducer} from '#/plugin/team/tools/team/store'
import {TeamTool} from '#/plugin/team/tools/team/containers/tool'

export const App = () => ({
  component: TeamTool,
  store: reducer
})
