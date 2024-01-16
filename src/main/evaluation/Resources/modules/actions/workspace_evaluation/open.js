import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/evaluation/workspace/routing'
import {hasPermission} from '#/main/app/security'

export default (evaluations, refresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-eye',
  label: trans('open_evaluation', {}, 'actions'),
  target: route(evaluations[0], path),
  displayed: hasPermission('open', evaluations[0]),
  scope: ['object']
})
