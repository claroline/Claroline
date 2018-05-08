import {registerModals} from '#/main/core/layout/modal'

// reexport message modals
import {MODAL_SEND_MESSAGE} from '#/main/core/user/message/modals'

// user modals
import {MODAL_CHANGE_PASSWORD, ChangePasswordModal} from '#/main/core/user/modals/components/change-password'
import {MODAL_CHANGE_PUBLIC_URL, ChangePublicUrlModal} from '#/main/core/user/modals/components/change-public-url'

// register user modals
registerModals([
  [MODAL_CHANGE_PASSWORD, ChangePasswordModal],
  [MODAL_CHANGE_PUBLIC_URL, ChangePublicUrlModal]
])

export {
  MODAL_SEND_MESSAGE,
  MODAL_CHANGE_PASSWORD,
  MODAL_CHANGE_PUBLIC_URL
}
