/**
 * Courses picker modal.
 *
 * Displays the courses picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CoursesModal} from '#/plugin/cursus/modals/courses/containers/modal'

const MODAL_TRAINING_COURSES = 'MODAL_TRAINING_COURSES'

// make the modal available for use
registry.add(MODAL_TRAINING_COURSES, CoursesModal)

export {
  MODAL_TRAINING_COURSES
}
