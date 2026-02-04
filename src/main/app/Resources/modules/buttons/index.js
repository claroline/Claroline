/**
 * Exposes buttons implemented in the `app` module.
 */

// Implemented buttons (Component + name in registry)
import {ASYNC_BUTTON,    AsyncButton}    from '#/main/app/buttons/async'
import {CALLBACK_BUTTON, CallbackButton} from '#/main/app/buttons/callback'
import {DOWNLOAD_BUTTON, DownloadButton} from '#/main/app/buttons/download'
import {LINK_BUTTON,     LinkButton}     from '#/main/app/buttons/link'
import {MENU_BUTTON,     MenuButton}     from '#/main/app/buttons/menu'
import {MODAL_BUTTON,    ModalButton}    from '#/main/app/buttons/modal'
import {POPOVER_BUTTON,  PopoverButton}  from '#/main/app/buttons/popover'
import {REQUEST_BUTTON,  RequestButton}  from '#/main/app/buttons/request'
import {URL_BUTTON,      UrlButton}      from '#/main/app/buttons/url'

export {
  // button types
  ASYNC_BUTTON,
  CALLBACK_BUTTON,
  DOWNLOAD_BUTTON,
  LINK_BUTTON,
  MENU_BUTTON,
  MODAL_BUTTON,
  POPOVER_BUTTON,
  REQUEST_BUTTON,
  URL_BUTTON,

  // button components
  AsyncButton,
  CallbackButton,
  DownloadButton,
  LinkButton,
  MenuButton,
  ModalButton,
  PopoverButton,
  RequestButton,
  UrlButton
}
