import {reducer} from '#/main/core/administration/parameters/technical/store/reducer'
import {Tool} from '#/main/core/administration/parameters/technical/components/tool.jsx'

export const App = () => ({
  component: Tool,
  store: reducer,
  initialData: (initialData) => ({
    tool: {
      name: 'technical_settings',
      currentContext: initialData.currentContext
    },

    parameters: {
      data: initialData.parameters,
      originalData: initialData.parameters
    },


    tools: initialData.tools
  })
})
