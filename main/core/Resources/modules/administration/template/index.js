import {reducer} from '#/main/core/administration/template/store'
import {TemplateTool} from '#/main/core/administration/template/containers/tool'

export const App = () => ({
  component: TemplateTool,
  store: reducer
})
