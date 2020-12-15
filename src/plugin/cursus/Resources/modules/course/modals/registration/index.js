/**
 * Course registration modal.
 * Displays a modal to allow registration to a course.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {RegistrationModal} from '#/plugin/cursus/course/modals/registration/components/modal'

const MODAL_COURSE_REGISTRATION = 'MODAL_COURSE_REGISTRATION'

// make the modal available for use
registry.add(MODAL_COURSE_REGISTRATION, RegistrationModal)

export {
  MODAL_COURSE_REGISTRATION
}
