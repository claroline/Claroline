/**
 * Resource Creation modal.
 *
 * The creation is split into 3 modals :
 *   - A first one, where the user select the type to create
 *   - A second one, where the user can configure the resource
 *   - A third one, where the user can configure rights
 *
 * NB. Only the first modal is public to be sure resource creation
 * always follow the same process
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ResourceTypeModal} from '#/main/core/resource/modals/creation/components/resource-type'

// internal modals
import {constants} from '#/main/core/resource/modals/creation/constants'
import {ParametersModal} from '#/main/core/resource/modals/creation/components/parameters'
import {RightsModal} from '#/main/core/resource/modals/creation/components/rights'

const MODAL_RESOURCE_CREATION = 'MODAL_RESOURCE_CREATION'

// make the modal available for use
registry.add(MODAL_RESOURCE_CREATION, ResourceTypeModal)
registry.add(constants.MODAL_RESOURCE_CREATION_INTERNAL_PARAMETERS, ParametersModal)
registry.add(constants.MODAL_RESOURCE_CREATION_INTERNAL_RIGHTS, RightsModal)

export {
  MODAL_RESOURCE_CREATION
}
