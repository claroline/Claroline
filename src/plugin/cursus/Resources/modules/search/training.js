import {trans} from '#/main/app/intl'
import {route as toolRoute} from '#/main/core/tool/routing'
import {CourseCard} from '#/plugin/cursus/course/components/card'
import {route} from '#/plugin/cursus/routing'

export default {
  name: 'training',
  label: trans('courses', {}, 'cursus'),
  component: CourseCard,
  link: (result) => route(toolRoute('trainings')+ '/catalog', result)
}
