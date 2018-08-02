/**
 * Async button.
 * Triggers an async request.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {AsyncButton} from '#/main/app/buttons/async/containers/button'

const ASYNC_BUTTON = 'async'

// make the button available for use
registry.add(ASYNC_BUTTON, AsyncButton)

export {
  ASYNC_BUTTON
}
