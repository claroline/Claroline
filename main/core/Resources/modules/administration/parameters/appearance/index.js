import {reducer} from '#/main/core/administration/parameters/appearance/store/reducer'
import {Tool} from '#/main/core/administration/parameters/appearance/components/tool'

export const App = () => ({
  component: Tool,
  store: reducer,
  initialData: (initialData) => ({
    tool: {
      name: 'appearance_settings',
      context: initialData.context
    },

    parameters: {
      data: initialData.parameters,
      originalData: initialData.parameters
    },
    themes: {
      data: initialData.themes.data
    },
    iconSetChoices: initialData.iconSetChoices
  })
})
