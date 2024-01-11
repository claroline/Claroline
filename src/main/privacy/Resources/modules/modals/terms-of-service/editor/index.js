import {registry} from '#/main/app/modals/registry'

import {EditorModal} from '#/main/privacy/modals/terms-of-service/editor/containers/modal'

const MODAL_TOS_EDITOR = 'MODAL_TOS_EDITOR'

registry.add(MODAL_TOS_EDITOR, EditorModal)

export {
  MODAL_TOS_EDITOR
}
