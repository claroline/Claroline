import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {TermsOfServiceModal} from '#/main/privacy/modals/terms-of-service/components/modal'

const MODAL_TERMS_OF_SERVICE = 'MODAL_TERMS_OF_SERVICE'

// make the modal available for use
registry.add(MODAL_TERMS_OF_SERVICE, TermsOfServiceModal)

export {
  MODAL_TERMS_OF_SERVICE
}
