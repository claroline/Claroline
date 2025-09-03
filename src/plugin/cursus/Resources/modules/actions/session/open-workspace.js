import {declareAction} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

import {route as workspaceRoute} from '#/main/core/workspace'
import {hasPermission} from '#/main/app/security'

export default declareAction((sessions) => ({
  name: 'open-workspace',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('open-workspace', {}, 'actions'),
  target: sessions[0].workspace ? workspaceRoute(sessions[0].workspace) : '',
  displayed: !!sessions[0].workspace && hasPermission('open', sessions[0]),
  scope: ['object'],
  exact: true
}))
