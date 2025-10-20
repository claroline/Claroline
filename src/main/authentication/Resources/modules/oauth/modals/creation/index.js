
import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {OauthCreationModal} from '#/main/authentication/oauth/modals/creation/components/modal'

const MODAL_OAUTH_CREATION = 'MODAL_OAUTH_CREATION'

// make the modal available for use
registry.add(MODAL_OAUTH_CREATION, OauthCreationModal)

export {
  MODAL_OAUTH_CREATION
}
