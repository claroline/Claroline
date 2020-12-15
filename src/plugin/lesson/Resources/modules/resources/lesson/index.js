import {reducer} from '#/plugin/lesson/resources/lesson/store'
import {LessonResource} from '#/plugin/lesson/resources/lesson/containers/resource'
import {LessonMenu} from '#/plugin/lesson/resources/lesson/containers/menu'

/**
 * Lesson resource application.
 */
export default {
  component: LessonResource,
  menu: LessonMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-lesson-lesson-resource']
}
