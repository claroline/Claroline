/**
 * Workspace Parameters modal.
 * Displays a form to configure the workspace.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {EvidenceModal} from '#/plugin/open-badge/tools/badges/modals/evidence/containers/modal'

const MODAL_BADGE_EVIDENCE = 'MODAL_BADGE_EVIDENCE'

// make the modal available for use
registry.add(MODAL_BADGE_EVIDENCE, EvidenceModal)

export {
  MODAL_BADGE_EVIDENCE
}
