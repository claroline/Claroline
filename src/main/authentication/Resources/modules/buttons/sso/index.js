/**
 * SSO button.
 * Triggers the standard SSO login process.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {SsoButton} from '#/main/authentication/buttons/sso/components/button'

const SSO_BUTTON = 'sso'

// make the button available for use
registry.add(SSO_BUTTON, SsoButton)

export {
  SSO_BUTTON,
  SsoButton
}
