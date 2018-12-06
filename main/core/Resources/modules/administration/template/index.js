import {reducer} from '#/main/core/administration/template/store'
import {TemplateTool} from '#/main/core/administration/template/components/tool'

export const App = () => ({
  component: TemplateTool,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData)
})