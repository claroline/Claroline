/**
 * Link button.
 * Navigates inside the application router.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {LinkButton} from '#/main/app/buttons/link/components/button'

const LINK_BUTTON = 'link'

// make the button available for use
registry.add(LINK_BUTTON, LinkButton)

export {
  LINK_BUTTON,
  LinkButton
}
