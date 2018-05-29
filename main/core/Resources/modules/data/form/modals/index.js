import {registry} from '#/main/app/modals/registry'

import {MODAL_CONFIGURE_FIELD, ConfigureFieldModal} from '#/main/core/data/form/modals/components/configure-field.jsx'

// make the modal available for use
registry.add(MODAL_CONFIGURE_FIELD, ConfigureFieldModal)

export {
  MODAL_CONFIGURE_FIELD
}