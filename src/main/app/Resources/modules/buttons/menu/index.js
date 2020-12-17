/**
 * Menu button.
 * Opens a contextual menu containing actions.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {MenuButton} from '#/main/app/buttons/menu/components/button'

const MENU_BUTTON = 'menu'

// make the button available for use
registry.add(MENU_BUTTON, MenuButton)

export {
  MENU_BUTTON,
  MenuButton
}
