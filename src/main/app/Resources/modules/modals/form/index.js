/**
 * Form modal.
 * Displays a modal which contains a standard claroline form.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {FormDataModal} from '#/main/app/modals/form/components/data'

const MODAL_DATA_FORM = 'MODAL_DATA_FORM'

// make the modal available for use
registry.add(MODAL_DATA_FORM, FormDataModal)

export {
  MODAL_DATA_FORM
}
