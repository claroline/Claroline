/**
 * Badges picker modal.
 *
 * Displays the badges picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {BadgesModal} from '#/plugin/open-badge/modals/badges/containers/modal'

const MODAL_BADGES = 'MODAL_BADGES'

// make the modal available for use
registry.add(MODAL_BADGES, BadgesModal)

export {
  MODAL_BADGES
}
