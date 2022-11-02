/**
 * Role tools rights modal.
 * Displays a form to configure the role tools rights.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RightsModal} from '#/main/community/tools/community/role/modals/rights/containers/modal'

const MODAL_ROLE_RIGHTS = 'MODAL_ROLE_RIGHTS'

// make the modal available for use
registry.add(MODAL_ROLE_RIGHTS, RightsModal)

export {
  MODAL_ROLE_RIGHTS
}
