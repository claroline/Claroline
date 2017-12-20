import {registerModals} from '#/main/core/layout/modal'

import {MODAL_SEND_MESSAGE, SendMessageModal} from '#/main/core/user/message/modals/components/send-message.jsx'

// register message modals
registerModals([
  [MODAL_SEND_MESSAGE, SendMessageModal]
])

export {
  MODAL_SEND_MESSAGE
}
