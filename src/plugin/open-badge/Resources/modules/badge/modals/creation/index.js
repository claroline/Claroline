/**
 * Badge creation modal.
 * Displays a modal to create a new badge.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CreationModal} from '#/plugin/open-badge/badge/modals/creation/containers/modal'

const MODAL_BADGE_CREATION = 'MODAL_BADGE_CREATION'

// make the modal available for use
registry.add(MODAL_BADGE_CREATION, CreationModal)

export {
  MODAL_BADGE_CREATION
}
