import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route as toolRoute} from '#/main/core/tool/routing'

import {route} from '#/plugin/cursus/routing'
import {CourseCard} from '#/plugin/cursus/course/components/card'

export default {
  name: 'courses',
  icon: 'fa fa-fw fa-cubes',
  parameters: {
    primaryAction: (course) => ({
      type: URL_BUTTON,
      target: `#${route(toolRoute('trainings')+'/catalog', course)}`
    }),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }
    ],
    card: CourseCard
  }
}
