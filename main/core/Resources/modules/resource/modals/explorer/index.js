/**
 * Resource Explorer modal.
 *
 * Displays the resources explorer inside the modale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ExplorerModal} from '#/main/core/resource/modals/explorer/containers/modal'

const MODAL_RESOURCE_EXPLORER = 'MODAL_RESOURCE_EXPLORER'

// make the modal available for use
registry.add(MODAL_RESOURCE_EXPLORER, ExplorerModal)

export {
  MODAL_RESOURCE_EXPLORER
}
