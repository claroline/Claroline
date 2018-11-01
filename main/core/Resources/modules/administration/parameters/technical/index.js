import {reducer} from '#/main/core/administration/parameters/technical/store/reducer'
import {Tool} from '#/main/core/administration/parameters/technical/components/tool.jsx'

export const App = () => ({
  component: Tool,
  store: reducer,
  initialData: (initialData) => {
    return {
      parameters: {
        data: initialData.parameters,
        originalData: initialData.parameters
      }
    }
  }
})
