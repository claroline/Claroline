/**
 * Tags modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TagsModal} from '#/plugin/tags/modals/tags/components/modal'

const MODAL_TAGS = 'MODAL_TAGS'

// make the modal available for use
registry.add(MODAL_TAGS, TagsModal)

export {
  MODAL_TAGS
}
