/**
 * Competency resources links modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ResourcesLinksModal} from '#/plugin/competency/modals/resources-links/containers/modal'

const MODAL_COMPETENCY_RESOURCES_LINKS = 'MODAL_COMPETENCY_RESOURCES_LINKS'

// make the modal available for use
registry.add(MODAL_COMPETENCY_RESOURCES_LINKS, ResourcesLinksModal)

export {
  MODAL_COMPETENCY_RESOURCES_LINKS
}
