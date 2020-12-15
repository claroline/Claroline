/**
 * Url button.
 * Opens an external url.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {UrlButton} from '#/main/app/buttons/url/components/button'

const URL_BUTTON = 'url'

// make the button available for use
registry.add(URL_BUTTON, UrlButton)

export {
  URL_BUTTON,
  UrlButton
}
