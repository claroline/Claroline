/**
 * User public url modal.
 * Displays a form to configure the user public URL.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {PublicUrlModal} from '#/main/core/user/modals/public-url/components/public-url'

const MODAL_USER_PUBLIC_URL = 'MODAL_USER_PUBLIC_URL'

// make the modal available for use
registry.add(MODAL_USER_PUBLIC_URL, PublicUrlModal)

export {
  MODAL_USER_PUBLIC_URL
}
