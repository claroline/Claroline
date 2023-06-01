import { registry } from '#/main/app/modals/registry'

// gets the modal component
import { TermsModal } from '#/main/privacy/account/privacy/modals/terms/containers/modal'

const MODAL_TERMS_OF_SERVICE = 'MODAL_THERM_OF_SERVICE'

// make the modal available for use
registry.add(MODAL_TERMS_OF_SERVICE, TermsModal)

export {
  MODAL_TERMS_OF_SERVICE
}