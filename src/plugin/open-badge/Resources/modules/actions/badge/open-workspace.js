import {constants, declareAction} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

import {route as workspaceRoute} from '#/main/core/workspace'
import {hasPermission} from '#/main/app/security'
import {matchPath} from 'react-router-dom'

export default declareAction((badges) => {
  let inWorkspace = false
  if (badges[0].workspace) {
    inWorkspace = !!matchPath(window.location.hash.replace('#', ''), {
      path: workspaceRoute(badges[0].workspace),
      exact: false
    })
  }

  return ({
    name: 'open-workspace',
    type: LINK_BUTTON,
    icon: 'fa fa-fw fa-book',
    label: trans('open-workspace', {}, 'actions'),
    target: badges[0].workspace ? workspaceRoute(badges[0].workspace) : '',
    displayed: !!badges[0].workspace && !inWorkspace && hasPermission('open', badges[0]),
    scope: ['object'],
    set: [constants.ACTION_SET_LIST],
    exact: true
  })
})
