/**
 * Tools rights modal.
 * Displays a form to configure the tool rights.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RightsModal} from '#/main/core/tool/modals/rights/containers/modal'

const MODAL_TOOL_RIGHTS = 'MODAL_TOOL_RIGHTS'

// make the modal available for use
registry.add(MODAL_TOOL_RIGHTS, RightsModal)

export {
  MODAL_TOOL_RIGHTS
}
