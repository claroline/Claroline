/**
 * OAuth Parameters modal.
 * Displays a form to configure an OAuth client.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {OauthFormModal} from '#/main/authentication/oauth/modals/form/components/modal'

const MODAL_OAUTH_FORM = 'MODAL_OAUTH_FORM'

// make the modal available for use
registry.add(MODAL_OAUTH_FORM, OauthFormModal)

export {
  MODAL_OAUTH_FORM
}
