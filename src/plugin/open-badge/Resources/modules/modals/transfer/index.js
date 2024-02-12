import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TransferModal} from '#/plugin/open-badge/modals/transfer/containers/transfer'

const MODAL_TRANSFER = 'MODAL_TRANSFER'

// make the modal available for use
registry.add(MODAL_TRANSFER, TransferModal)

export {
  MODAL_TRANSFER
}
