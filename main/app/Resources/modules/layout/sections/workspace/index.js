
import {WorkspaceMain} from '#/main/app/layout/sections/workspace/containers/main'
import {reducer} from '#/main/app/layout/sections/workspace/store'

export const App = () => ({
  component: WorkspaceMain,
  store: reducer
})
