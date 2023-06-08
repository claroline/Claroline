import { registry } from '#/main/app/modals/registry'
import { DpoModal } from '#/main/privacy/administration/privacy/modals/dpo/containers/modal'

const MODAL_INFO_DPO = 'MODAL_INFO_DPO'

registry.add(MODAL_INFO_DPO, DpoModal)

export {
  MODAL_INFO_DPO
}