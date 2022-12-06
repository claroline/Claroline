import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/workspace/routing'
import {constants} from '#/main/community/constants'

/**
 * View as the Role.
 * It's only available for Workspace roles.
 */
export default (roles) => ({
  name: 'view-as',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-mask',
  label: trans('view-as', {}, 'actions'),
  displayed: constants.ROLE_WORKSPACE === roles[0].type && hasPermission('edit', roles[0]),
  target: url(['claro_index', {}], {view_as: roles[0].name}) + '#' + (roles[0].workspace ? route(roles[0].workspace) : ''),
  group: trans('management'),
  scope: ['object']
})
