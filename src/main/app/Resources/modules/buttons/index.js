/**
 * Exposes buttons implemented in the `app` module.
 */

// Implemented buttons (Component + name in registry)
import {ASYNC_BUTTON,    AsyncButton}    from '#/main/app/buttons/async'
import {CALLBACK_BUTTON, CallbackButton} from '#/main/app/buttons/callback'
import {DOWNLOAD_BUTTON, DownloadButton} from '#/main/app/buttons/download'
import {EMAIL_BUTTON,    EmailButton}    from '#/main/app/buttons/email'
import {LINK_BUTTON,     LinkButton}     from '#/main/app/buttons/link'
import {MENU_BUTTON,     MenuButton}     from '#/main/app/buttons/menu'
import {MODAL_BUTTON,    ModalButton}    from '#/main/app/buttons/modal'
import {POPOVER_BUTTON,  PopoverButton}  from '#/main/app/buttons/popover'
import {TOGGLE_BUTTON,   ToggleButton}  from '#/main/app/buttons/toggle'
import {URL_BUTTON,      UrlButton}      from '#/main/app/buttons/url'

export {
  // button types
  ASYNC_BUTTON,
  CALLBACK_BUTTON,
  DOWNLOAD_BUTTON,
  EMAIL_BUTTON,
  LINK_BUTTON,
  MENU_BUTTON,
  MODAL_BUTTON,
  POPOVER_BUTTON,
  TOGGLE_BUTTON,
  URL_BUTTON,

  // button components
  AsyncButton,
  CallbackButton,
  DownloadButton,
  EmailButton,
  LinkButton,
  MenuButton,
  ModalButton,
  PopoverButton,
  ToggleButton,
  UrlButton
}
