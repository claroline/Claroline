/**
 * Step details modal.
 * Displays a modal with information about the step.
 */

import {registry} from '#/main/app/modals/registry'

import {StepDetailsModal} from '#/plugin/path/analytics/workspace/path/modals/step-details/components/modal'

const MODAL_STEP_DETAILS = 'MODAL_STEP_DETAILS'

registry.add(MODAL_STEP_DETAILS, StepDetailsModal)

export {
  MODAL_STEP_DETAILS
}
