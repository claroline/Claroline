/**
 * Courses picker modal.
 *
 * Displays the courses picker inside the modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CoursesModal} from '#/plugin/cursus/modals/courses/containers/modal'

const MODAL_COURSES = 'MODAL_COURSES'

// make the modal available for use
registry.add(MODAL_COURSES, CoursesModal)

export {
  MODAL_COURSES
}
