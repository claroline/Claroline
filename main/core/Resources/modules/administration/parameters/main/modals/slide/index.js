/**
 * Slide form modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SlideFormModal} from '#/main/core/administration/parameters/main/modals/slide/components/modal'

const MODAL_SLIDE_FORM = 'MODAL_SLIDE_FORM'

// make the modal available for use
registry.add(MODAL_SLIDE_FORM, SlideFormModal)

export {
  MODAL_SLIDE_FORM
}
