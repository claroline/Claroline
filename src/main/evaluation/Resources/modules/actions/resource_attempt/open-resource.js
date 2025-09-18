import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/resource'
import {declareAction} from '#/main/app/action'

export default declareAction((attempts) => ({
  name: 'open-resource',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-folder',
  label: trans('open-resource', {}, 'actions'),
  target: route(attempts[0].resourceNode),
  scope: ['object'],
  exact: true,
  default: true
}))
