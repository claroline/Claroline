import {HomeTool} from '#/main/core/tools/home/components/tool'
import {reducer} from '#/main/core/tools/home/store'

/**
 * HomeTool application.
 *
 * @constructor
 */
export const App = () => ({
  component: HomeTool,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData, {
    editable: !!initialData.editable,
    administration: !!initialData.administration,
    editor:{
      data: initialData.tabs || [],
      originalData: initialData.tabs || []
    }
  })
})
