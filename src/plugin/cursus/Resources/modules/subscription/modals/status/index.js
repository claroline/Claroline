import {registry} from '#/main/app/modals/registry'

import {StatusModal} from '#/plugin/cursus/subscription/modals/status/components/modal'

const MODAL_SUBSCRIPTION_STATUS = 'MODAL_SUBSCRIPTION_STATUS'

registry.add(MODAL_SUBSCRIPTION_STATUS, StatusModal)

export {
  MODAL_SUBSCRIPTION_STATUS
}
