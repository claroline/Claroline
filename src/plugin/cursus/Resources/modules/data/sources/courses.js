import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route as toolRoute} from '#/main/core/tool/routing'

import {param} from '#/main/app/config'
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
      }, {
        name: 'code',
        type: 'string',
        label: trans('code'),
        displayed: true
      }, {
        name: 'location',
        type: 'location',
        label: trans('location'),
        placeholder: trans('online_session', {}, 'cursus'),
        displayable: false,
        sortable: false
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        displayed: true,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CursusBundle\\Entity\\Course'
        }
      }, {
        name: 'pricing.price',
        alias: 'price',
        label: trans('price'),
        type: 'currency',
        displayable: param('pricing.enabled'),
        displayed: param('pricing.enabled'),
        filterable: param('pricing.enabled'),
        sortable: param('pricing.enabled')
      }, {
        name: 'display.order',
        alias: 'order',
        type: 'number',
        label: trans('order'),
        displayable: false,
        filterable: false
      }
    ],
    card: CourseCard
  }
}
