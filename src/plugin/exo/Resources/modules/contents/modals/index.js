import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ContentModal} from '#/plugin/exo/contents/modals/components/modal'

const MODAL_CONTENT = 'MODAL_CONTENT'

// make the modal available for use
registry.add(MODAL_CONTENT, ContentModal)

export {
  MODAL_CONTENT
}
