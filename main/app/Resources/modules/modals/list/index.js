/**
 * List modal.
 * Displays a modal which contains a list of data.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ListDataModal} from '#/main/app/modals/list/containers/data'

const MODAL_DATA_LIST = 'MODAL_DATA_LIST'

// make the modal available for use
registry.add(MODAL_DATA_LIST, ListDataModal)

export {
  MODAL_DATA_LIST
}
