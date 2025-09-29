/**
 * List functional logs for a content.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {FunctionalLogsModal} from '#/main/log/modals/functional/components/modal'

const MODAL_FUNCTIONAL_LOGS = 'MODAL_FUNCTIONAL_LOGS'

// make the modal available for use
registry.add(MODAL_FUNCTIONAL_LOGS, FunctionalLogsModal)

export {
  MODAL_FUNCTIONAL_LOGS
}
