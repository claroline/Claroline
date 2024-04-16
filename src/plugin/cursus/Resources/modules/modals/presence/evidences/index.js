/**
 * Evidence modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EvidenceModal} from '#/plugin/cursus/modals/presence/evidences/components/modal'

const MODAL_EVIDENCE = 'MODAL_EVIDENCE'

// make the modal available for use
registry.add(MODAL_EVIDENCE, EvidenceModal)

export {
  MODAL_EVIDENCE
}
