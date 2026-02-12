/**
 * Clipboard button.
 * Triggers a copy to the user clipboard.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {ClipboardButton} from '#/main/app/buttons/clipboard/components/button'

const CLIPBOARD_BUTTON = 'clipboard'

// make the button available for use
registry.add(CLIPBOARD_BUTTON, ClipboardButton)

export {
  CLIPBOARD_BUTTON,
  ClipboardButton
}
