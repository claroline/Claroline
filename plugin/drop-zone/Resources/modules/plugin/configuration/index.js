import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {registerModals} from '#/main/core/layout/modal'

import {reducer} from '#/plugin/drop-zone/plugin/configuration/reducer'
import {Tools} from '#/plugin/drop-zone/plugin/configuration/components/tools.jsx'
import {CompilatioFormModal} from '#/plugin/drop-zone/plugin/configuration/components/modal/compilatio-form-modal.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.dropzone-plugin-container',

  // app main component
  Tools,

  // app store configuration
  reducer
)

registerModals([
  ['MODAL_COMPILATIO_FORM', CompilatioFormModal]
])
