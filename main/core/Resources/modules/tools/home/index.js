import {bootstrap} from '#/main/app/bootstrap'

import {HomeTool} from '#/main/core/tools/home/components/tool'
import {reducer} from '#/main/core/tools/home/reducer'

bootstrap(
  '.home-container',
  HomeTool,
  reducer,
  (initialData) => Object.assign({}, initialData, {
    editable: !!initialData.editable,
    editor:{
      data: {
        tabs: initialData.tabs
      },
      originalData: {
        tabs: initialData.tabs
      }
    }
  })
)
