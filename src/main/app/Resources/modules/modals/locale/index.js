/**
 * Locale modal.
 * Displays a modal which permits to change the current locale.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {LocaleModal} from '#/main/app/modals/locale/components/modal'

const MODAL_LOCALE = 'MODAL_LOCALE'

// make the modal available for use
registry.add(MODAL_LOCALE, LocaleModal)

export {
  MODAL_LOCALE
}
