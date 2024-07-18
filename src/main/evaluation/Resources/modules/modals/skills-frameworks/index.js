/**
 * Skills frameworks picker modal.
 *
 * Displays the skills frameworks picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SkillsFrameworksModal} from '#/main/evaluation/modals/skills-frameworks/components/modal'

const MODAL_SKILLS_FRAMEWORKS = 'MODAL_SKILLS_FRAMEWORKS'

// make the modal available for use
registry.add(MODAL_SKILLS_FRAMEWORKS, SkillsFrameworksModal)

export {
  MODAL_SKILLS_FRAMEWORKS
}
