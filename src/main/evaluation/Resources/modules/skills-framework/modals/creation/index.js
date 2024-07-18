/**
 * Skills frameworks creation modal.
 *
 * Displays the skills framework creation inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CreationModal} from '#/main/evaluation/skills-framework/modals/creation/containers/main'

const MODAL_SKILLS_FRAMEWORKS_CREATION = 'MODAL_SKILLS_FRAMEWORKS_CREATION'

// make the modal available for use
registry.add(MODAL_SKILLS_FRAMEWORKS_CREATION, CreationModal)

export {
  MODAL_SKILLS_FRAMEWORKS_CREATION
}
