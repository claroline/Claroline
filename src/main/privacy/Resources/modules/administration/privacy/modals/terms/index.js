import { registry } from '#/main/app/modals/registry'
import { TermsModal } from '#/main/privacy/administration/privacy/modals/terms/containers/modal'

const MODAL_TERMS_OF_SERVICE = 'MODAL_THERM_OF_SERVICE'

registry.add(MODAL_TERMS_OF_SERVICE, TermsModal)

export {
  MODAL_TERMS_OF_SERVICE
}
