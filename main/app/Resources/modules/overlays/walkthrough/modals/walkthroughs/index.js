/**
 * Walkthroughs modal.
 * Displays a modal with all the walkthroughs available for the ui section.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {WalkthroughsModal} from '#/main/app/overlays/walkthrough/modals/walkthroughs/containers/modal'

const MODAL_WALKTHROUGHS = 'MODAL_WALKTHROUGHS'

// make the modal available for use
registry.add(MODAL_WALKTHROUGHS, WalkthroughsModal)

export {
  MODAL_WALKTHROUGHS
}
