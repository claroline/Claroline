import {declareAction} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

import {route} from '#/plugin/agenda/tools/agenda/routing'

export default declareAction((events, refresher, path) => ({
  name: 'show-calendar',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-calendar',
  label: trans('show-calendar', {}, 'actions'),
  target: route(path, 'month', events[0].start),
  scope: ['object']
}))
