/**
 * Session event about modal.
 * Displays information about the session event (used for integration with agenda).
 */

import {registry} from '#/main/app/modals/registry'

import {EventAboutModal} from '#/plugin/cursus/event/modals/about/components/modal'

const MODAL_TRAINING_EVENT_ABOUT = 'MODAL_TRAINING_EVENT_ABOUT'

registry.add(MODAL_TRAINING_EVENT_ABOUT, EventAboutModal)

export {
  MODAL_TRAINING_EVENT_ABOUT
}
