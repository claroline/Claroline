import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (users, refresher, path, currentUser) => ({
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-line-chart',
  label: trans('show_tracking'),
  displayed: false && (hasPermission('administrate', users[0]) || users[0].id === get(currentUser, 'id')),
  target: ['claro_user_tracking', {publicUrl: users[0].meta.publicUrl}],
  scope: ['object']
})
