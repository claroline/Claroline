/**
 * Evidence aboout modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EvidenceAboutModal} from '#/plugin/cursus/modals/presence/about/components/modal'

const MODAL_EVIDENCE_ABOUT = 'MODAL_EVIDENCE_ABOUT'

// make the modal available for use
registry.add(MODAL_EVIDENCE_ABOUT, EvidenceAboutModal)

export {
  MODAL_EVIDENCE_ABOUT
}
