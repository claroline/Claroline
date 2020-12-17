import {registry} from '#/main/app/modals/registry'

import {FileFormModal} from '#/main/core/resources/file/modals/form/containers/modal'

const MODAL_FILE_FORM = 'MODAL_FILE_FORM'

registry.add(MODAL_FILE_FORM, FileFormModal)

export {
  MODAL_FILE_FORM
}
