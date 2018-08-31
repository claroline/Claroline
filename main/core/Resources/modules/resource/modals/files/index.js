/**
 * Resource Files Creation modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ResourceFilesCreationModal} from '#/main/core/resource/modals/files/containers/modal'

const MODAL_RESOURCE_FILES_CREATION = 'MODAL_RESOURCE_FILES_CREATION'

// make the modal available for use
registry.add(MODAL_RESOURCE_FILES_CREATION, ResourceFilesCreationModal)

export {
  MODAL_RESOURCE_FILES_CREATION
}
