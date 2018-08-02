/**
 * Field parameters modal.
 * Displays a modal to configure a form field.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ParametersModal} from '#/main/app/data/fields/modals/parameters/components/parameters'

const MODAL_FIELD_PARAMETERS = 'MODAL_FIELD_PARAMETERS'

// make the modal available for use
registry.add(MODAL_FIELD_PARAMETERS, ParametersModal)

export {
  MODAL_FIELD_PARAMETERS
}
