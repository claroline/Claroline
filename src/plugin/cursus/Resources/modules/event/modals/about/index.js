/**
 * Training event About modal.
 * Displays general information about the training event.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {AboutModal} from '#/plugin/cursus/event/modals/about/components/about'

const MODAL_TRAINING_EVENT_ABOUT = 'MODAL_TRAINING_EVENT_ABOUT'

// make the modal available for use
registry.add(MODAL_TRAINING_EVENT_ABOUT, AboutModal)

export {
  MODAL_TRAINING_EVENT_ABOUT
}
