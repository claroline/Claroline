import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SendingModal} from '#/plugin/announcement/resources/announcement/modals/sending/containers/modal'

const MODAL_ANNOUNCEMENT_SENDING = 'MODAL_ANNOUNCEMENT_SENDING'

// make the modal available for use
registry.add(MODAL_ANNOUNCEMENT_SENDING, SendingModal)

export {
  MODAL_ANNOUNCEMENT_SENDING
}
