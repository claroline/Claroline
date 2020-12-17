import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SendingConfirmModal} from '#/plugin/announcement/resources/announcement/modals/sending-confirm/components/sending-confirm'

const MODAL_ANNOUNCEMENT_SENDING_CONFIRM = 'MODAL_ANNOUNCEMENT_SENDING_CONFIRM'

// make the modal available for use
registry.add(MODAL_ANNOUNCEMENT_SENDING_CONFIRM, SendingConfirmModal)

export {
  MODAL_ANNOUNCEMENT_SENDING_CONFIRM
}
