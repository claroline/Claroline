import {reducer} from '#/main/core/administration/parameters/main/store/reducer'
import {Tool} from '#/main/core/administration/parameters/main/components/tool.jsx'

export const App = () => ({
  component: Tool,
  store: reducer,
  initialData: (initialData) => {
    return {
      parameters: {
        data: initialData.parameters,
        originalData: initialData.parameters
      },
      availablesLocales: initialData.availablesLocales,
      portalResources: initialData.portalResources
    }
  }
})
