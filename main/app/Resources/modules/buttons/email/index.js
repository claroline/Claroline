/**
 * Email button.
 * Opens the user standard mailer.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {EmailButton} from '#/main/app/buttons/email/components/button'

const EMAIL_BUTTON = 'email'

// make the button available for use
registry.add(EMAIL_BUTTON, EmailButton)

export {
  EMAIL_BUTTON,
  EmailButton
}
