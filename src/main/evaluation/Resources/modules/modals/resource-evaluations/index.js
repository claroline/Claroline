/**
 * Resource evaluations modal.
 *
 * Displays all the ResourceEvaluations of a ResourceUserEvaluation.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ResourceEvaluationsModal} from '#/main/evaluation/modals/resource-evaluations/containers/modal'

const MODAL_RESOURCE_EVALUATIONS = 'MODAL_RESOURCE_EVALUATIONS'

// make the modal available for use
registry.add(MODAL_RESOURCE_EVALUATIONS, ResourceEvaluationsModal)

export {
  MODAL_RESOURCE_EVALUATIONS
}
