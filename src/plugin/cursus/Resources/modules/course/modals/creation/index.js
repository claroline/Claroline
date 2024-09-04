import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CreationModal} from '#/plugin/cursus/course/modals/creation/containers/modal'

const MODAL_COURSE_TYPE_CREATION = 'MODAL_COURSE_TYPE_CREATION'

// make the modal available for use
registry.add(MODAL_COURSE_TYPE_CREATION, CreationModal)

export {
  MODAL_COURSE_TYPE_CREATION
}
