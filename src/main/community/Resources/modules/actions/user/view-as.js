import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (users, refresher, path, currentUser) => ({
  name: 'view-as',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-mask',
  label: trans('view-as', {}, 'actions'),
  displayed: currentUser && users[0].id !== currentUser.id && hasPermission('administrate', users[0]),
  target: url(['claro_index', {_switch: users[0].username}])+'#'+path,
  group: trans('management'),
  scope: ['object']
})
