import {reducer} from '#/main/core/administration/parameters/appearance/store/reducer'
import {Tool} from '#/main/core/administration/parameters/appearance/components/tool.jsx'

export const App = () => ({
  component: Tool,
  store: reducer,
  initialData: (initialData) => {
    return {
      parameters: {
        data: initialData.parameters,
        originalData: initialData.parameters
      },
      themes: {
        data: initialData.themes.data
      },
      iconSetChoices: initialData.iconSetChoices
    }
  }
})
