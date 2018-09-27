// TODO : Removes as it is only used by ClacoForm

import {registry} from '#/main/app/modals/registry'

import {UserPickerModal} from '#/main/core/layout/modal/user-picker/components/user-picker'

const MODAL_USER_PICKER = 'MODAL_USER_PICKER'

registry.add(MODAL_USER_PICKER, UserPickerModal)

export {
  MODAL_USER_PICKER
}