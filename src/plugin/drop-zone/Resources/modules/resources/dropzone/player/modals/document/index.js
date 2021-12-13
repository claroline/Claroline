import {registry} from '#/main/app/modals/registry'

import {AddDocumentModal} from '#/plugin/drop-zone/resources/dropzone/player/modals/document/components/modal'

const MODAL_ADD_DOCUMENT = 'MODAL_ADD_DOCUMENT'

registry.add(MODAL_ADD_DOCUMENT, AddDocumentModal)

export {
  MODAL_ADD_DOCUMENT
}
