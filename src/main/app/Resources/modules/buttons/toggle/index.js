/**
 * Toggle button.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {ToggleButton} from '#/main/app/buttons/toggle/components/button'

const TOGGLE_BUTTON = 'toggle'

// make the button available for use
registry.add(TOGGLE_BUTTON, ToggleButton)

export {
  TOGGLE_BUTTON,
  ToggleButton
}
