/**
 * Material form modal.
 * Displays a form to configure a material.
 */

import {registry} from '#/main/app/modals/registry'

import {MaterialParametersModal} from '#/main/core/tools/locations/material/modals/parameters/containers/modal'

const MODAL_MATERIAL_PARAMETERS = 'MODAL_MATERIAL_PARAMETERS'

registry.add(MODAL_MATERIAL_PARAMETERS, MaterialParametersModal)

export {
  MODAL_MATERIAL_PARAMETERS
}
