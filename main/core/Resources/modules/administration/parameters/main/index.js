import {reducer} from '#/main/core/administration/parameters/main/store/reducer'
import {Tool} from '#/main/core/administration/parameters/main/components/tool.jsx'

export const App = () => ({
  component: Tool,
  store: reducer,
  initialData: (initialData) => ({
    tool: {
      name: 'main_settings',
      context: initialData.context
    },

    parameters: {
      data: initialData.parameters,
      originalData: initialData.parameters
    },
    availableLocales: initialData.availableLocales,
    portalResources: initialData.portalResources
  })
})
