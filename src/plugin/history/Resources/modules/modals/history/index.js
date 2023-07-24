/**
 * History modal.
 * Displays the history (workspaces and resources) of the current user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {HistoryModal} from '#/plugin/history/modals/history/containers/modal'

const MODAL_HISTORY = 'MODAL_HISTORY'

// make the modal available for use
registry.add(MODAL_HISTORY, HistoryModal)

export {
  MODAL_HISTORY
}
