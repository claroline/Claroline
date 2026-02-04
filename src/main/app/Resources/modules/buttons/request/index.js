/**
 * Request button.
 * Triggers an API request.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {RequestButton} from '#/main/app/buttons/request/components/button'

const REQUEST_BUTTON = 'request'

// make the button available for use
registry.add(REQUEST_BUTTON, RequestButton)

export {
  REQUEST_BUTTON,
  RequestButton
}
