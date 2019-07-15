import {reducer} from '#/plugin/lesson/resources/lesson/store'
import {LessonResource} from '#/plugin/lesson/resources/lesson/containers/resource'

/**
 * Lesson resource application.
 */
export default {
  component: LessonResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-lesson-lesson-resource']
}
