/**
 * Tags modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ObjectTagsModal} from '#/plugin/tag/modals/object-tags/containers/modal'

const MODAL_OBJECT_TAGS = 'MODAL_OBJECT_TAGS'

// make the modal available for use
registry.add(MODAL_OBJECT_TAGS, ObjectTagsModal)

export {
  MODAL_OBJECT_TAGS
}
