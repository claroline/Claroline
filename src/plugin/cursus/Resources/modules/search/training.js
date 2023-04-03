import {trans} from '#/main/app/intl'

import {CourseCard} from '#/plugin/cursus/course/components/card'
import {route} from '#/plugin/cursus/routing'

export default {
  name: 'training',
  label: trans('courses', {}, 'cursus'),
  component: CourseCard,
  link: (result) => route(result)
}
