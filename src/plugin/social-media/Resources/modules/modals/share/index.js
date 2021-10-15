/**
 * Tags picker modal.
 *
 * Displays the tags picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ShareModal} from '#/plugin/social-media/modals/share/components/modal'

const MODAL_SHARE = 'MODAL_SHARE'

// make the modal available for use
registry.add(MODAL_SHARE, ShareModal)

export {
  MODAL_SHARE
}
