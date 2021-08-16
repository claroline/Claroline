/**
 * Training event About modal.
 * Displays general information about the training event.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SubscriptionModal} from '#/plugin/cursus/subscription/modals/about/components/about'

const MODAL_SUBSCRIPTION_ABOUT = 'MODAL_SUBSCRIPTION_ABOUT'

// make the modal available for use
registry.add(MODAL_SUBSCRIPTION_ABOUT, SubscriptionModal)

export {
  MODAL_SUBSCRIPTION_ABOUT
}
