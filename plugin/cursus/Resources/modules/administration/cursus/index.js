import {reducer} from '#/plugin/cursus/administration/cursus/store'
import {CursusTool} from '#/plugin/cursus/administration/cursus/components/tool'

export const App = () => ({
  component: CursusTool,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData)
})