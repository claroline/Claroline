import { registry } from '#/main/app/modals/registry'
import { DpoModal } from '#/main/privacy/administration/privacy/modals/dpo/containers/modal'

const MODAL_INFOS_DPO = 'MODAL_INFOS_DPO'

registry.add(MODAL_INFOS_DPO, DpoModal)

export {
  MODAL_INFOS_DPO
}