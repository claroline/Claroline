
import {Workspaces} from '#/main/core/workspace/list/components/list'
import {reducer} from '#/main/core/workspace/list/store'

export const App = () => ({
  component: Workspaces,
  store: reducer
})
