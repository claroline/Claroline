import {reducer} from '#/main/core/administration/parameters/main/store/reducer'
import {Tool} from '#/main/core/administration/parameters/main/containers/tool'

export const App = () => ({
  component: Tool,
  store: reducer,
  initialData: (initialData) => ({
    tool: {
      name: 'main_settings',
      currentContext: initialData.currentContext
    },

    parameters: {
      data: initialData.parameters,
      originalData: initialData.parameters
    },
    availableLocales: initialData.availableLocales
  })
})
