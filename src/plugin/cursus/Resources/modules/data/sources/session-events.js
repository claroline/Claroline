import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route as toolRoute} from '#/main/core/tool/routing'

import {route} from '#/plugin/cursus/routing'
import {EventCard} from '#/plugin/cursus/event/components/card'

export default {
  name: 'session-events',
  icon: 'fa fa-fw fa-cubes',
  parameters: {
    primaryAction: (session) => ({
      type: URL_BUTTON,
      target: '#'
      //target: `#${route(toolRoute('trainings')+'/catalog', session.course, session)}`
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
        displayed: false
      }
    ],
    card: EventCard
  }
}
