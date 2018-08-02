/**
 * Callback button.
 * Triggers a callback function.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

const CALLBACK_BUTTON = 'callback'

// make the button available for use
registry.add(CALLBACK_BUTTON, CallbackButton)

export {
  CALLBACK_BUTTON
}
