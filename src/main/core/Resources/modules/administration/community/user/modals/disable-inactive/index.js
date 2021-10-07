/**
 * User public url modal.
 * Displays a form to configure the user public URL.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {DisableInactiveModal} from '#/main/core/administration/community/user/modals/disable-inactive/containers/modal'

const MODAL_USER_DISABLE_INACTIVE = 'MODAL_USER_DISABLE_INACTIVE'

// make the modal available for use
registry.add(MODAL_USER_DISABLE_INACTIVE, DisableInactiveModal)

export {
  MODAL_USER_DISABLE_INACTIVE
}
