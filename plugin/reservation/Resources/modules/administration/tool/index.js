import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {registerModals} from '#/main/core/layout/modal'

import {reducer} from '#/plugin/reservation/administration/tool/reducer'
import {ReservationTool} from '#/plugin/reservation/administration/tool/components/tool.jsx'
import {
  MODAL_RESOURCE_TYPES,
  ResourceTypesModal
} from '#/plugin/reservation/administration/resource-type/components/modal/resource-types-modal.jsx'

registerModals([
  [MODAL_RESOURCE_TYPES, ResourceTypesModal]
])

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.reservation-tool-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  ReservationTool,

  // app store configuration
  reducer
)