/**
 * Popover button.
 * Opens a popover.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {PopoverButton} from '#/main/app/buttons/popover/components/button'

const POPOVER_BUTTON = 'popover'

// make the button available for use
registry.add(POPOVER_BUTTON, PopoverButton)

export {
  POPOVER_BUTTON,
  PopoverButton
}
