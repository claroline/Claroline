import { registry } from '#/main/app/modals/registry'

import { TermsOfServiceModal } from '#/main/privacy/account/privacy/modals/terms/components/modal'

const MODAL_TERMS_OF_SERVICE_CONSUME = 'MODAL_TERMS_OF_SERVICE_CONSUME'

registry.add(MODAL_TERMS_OF_SERVICE_CONSUME, TermsOfServiceModal)

export {
  MODAL_TERMS_OF_SERVICE_CONSUME
}