/**
 * Item media addition modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AddMediaModal} from '#/plugin/exo/data/types/medias/modals/editor/containers/modal'

const MODAL_ADD_MEDIA = 'MODAL_ADD_MEDIA'

// make the modal available for use
registry.add(MODAL_ADD_MEDIA, AddMediaModal)

export {
  MODAL_ADD_MEDIA
}
