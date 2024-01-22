import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/workspace/routing'

export default (evaluations) => ({
  name: 'open-workspace',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('open-workspace', {}, 'actions'),
  target: route(evaluations[0].workspace),
  scope: ['object']
})
