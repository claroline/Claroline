/**
 * Color button.
 * Opens a color picker.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {ColorButton} from '#/main/theme/buttons/color/components/button'

const COLOR_BUTTON = 'color'

// make the button available for use
registry.add(COLOR_BUTTON, ColorButton)

export {
  COLOR_BUTTON,
  ColorButton
}
