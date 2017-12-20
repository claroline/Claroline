import {registerModals} from '#/main/core/layout/modal'

import {MODAL_CONFIGURE_FIELD, ConfigureFieldModal} from '#/main/core/data/form/modals/components/configure-field.jsx'

// register message modals
registerModals([
  [MODAL_CONFIGURE_FIELD, ConfigureFieldModal]
])

export {
  MODAL_CONFIGURE_FIELD
}