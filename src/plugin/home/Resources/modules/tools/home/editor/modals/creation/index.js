/**
 * Tab Creation modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TabCreationModal} from '#/plugin/home/tools/home/editor/modals/creation/containers/modal'

const MODAL_HOME_CREATION = 'MODAL_HOME_CREATION'

// make the modal available for use
registry.add(MODAL_HOME_CREATION, TabCreationModal)

export {
  MODAL_HOME_CREATION
}
