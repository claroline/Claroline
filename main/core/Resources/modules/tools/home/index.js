import {bootstrap} from '#/main/app/bootstrap'

import {registerModals} from '#/main/core/layout/modal'

import {MODAL_ADD_WIDGET, AddWidgetModal} from '#/main/core/widget/modals/components/add-widget'
import {MODAL_EDIT_WIDGET, EditWidgetModal} from '#/main/core/widget/modals/components/edit-widget'

import {HomeTool} from '#/main/core/tools/home/components/tool'
import {reducer} from '#/main/core/tools/home/reducer'

registerModals([
  [MODAL_ADD_WIDGET,  AddWidgetModal],
  [MODAL_EDIT_WIDGET, EditWidgetModal]
])

bootstrap(
  '.home-container',
  HomeTool,
  reducer,
  (initialData) => Object.assign({}, initialData, {
    editor: {
      data: {
        widgets: initialData.widgets,
        tabs: initialData.widgets
      }
    }
  })
)
