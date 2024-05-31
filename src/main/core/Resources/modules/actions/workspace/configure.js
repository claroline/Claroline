import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/workspace/routing'

export default (workspaces) => ({
  name: 'configure',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  displayed: -1 !== workspaces.findIndex(workspace => hasPermission('administrate', workspace)),
  target: `${route(workspaces[0])}/edit`,
  group: trans('management'),
  scope: ['object'],
  primary: true
})
