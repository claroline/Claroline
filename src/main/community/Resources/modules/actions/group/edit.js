import get from 'lodash/get'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {route} from '#/main/community/group/routing'

export default (groups, refresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  target: route(groups[0], path) + '/edit',
  displayed: hasPermission('edit', groups[0]),
  disabled: get(groups[0], 'meta.readOnly'),
  primary: true,
  group: trans('management'),
  scope: ['object']
})
