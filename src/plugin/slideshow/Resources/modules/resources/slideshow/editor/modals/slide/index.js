/**
 * Slide form modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SlideModal} from '#/plugin/slideshow/resources/slideshow/editor/modals/slide/containers/modal'

const MODAL_SLIDE = 'MODAL_SLIDE'

// make the modal available for use
registry.add(MODAL_SLIDE, SlideModal)

export {
  MODAL_SLIDE
}
