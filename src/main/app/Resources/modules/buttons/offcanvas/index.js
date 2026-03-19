/**
 * Offcanvas button.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {OffcanvasButton} from '#/main/app/buttons/offcanvas/components/button'

const OFFCANVAS_BUTTON = 'offcanvas'

// make the button available for use
registry.add(OFFCANVAS_BUTTON, OffcanvasButton)

export {
  OFFCANVAS_BUTTON,
  OffcanvasButton
}
