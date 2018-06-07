/**
 * Form modal.
 * Displays a modal which contains the content of another page inside an IFrame.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {DataFormModal} from '#/main/app/modals/form/components/data-form'

const MODAL_DATA_FORM = 'MODAL_DATA_FORM'

// make the modal available for use
registry.add(MODAL_DATA_FORM, DataFormModal)

export {
  MODAL_DATA_FORM
}
