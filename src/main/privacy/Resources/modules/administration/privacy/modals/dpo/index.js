import { registry } from '#/main/app/modals/registry'

// gets the modal component
import { DpoModal } from '#/main/privacy/administration/privacy/modals/dpo/containers/modal'

const MODAL_INFOS_DPO = 'MODAL_INFOS_DPO'

// make the modal available for use
registry.add(MODAL_INFOS_DPO, DpoModal)

export {
  MODAL_INFOS_DPO
}