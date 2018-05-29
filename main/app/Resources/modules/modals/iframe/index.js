/**
 * IFrame modal.
 * Displays a modal which contains the content of another page inside an IFrame.
 *
 * NB. You should use it with caution and only to embed external content (eg. YouTube video).
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {IframeModal} from '#/main/app/modals/iframe/components/iframe'

const MODAL_IFRAME = 'MODAL_IFRAME'

// make the modal available for use
registry.add(MODAL_IFRAME, IframeModal)

export {
  MODAL_IFRAME
}
