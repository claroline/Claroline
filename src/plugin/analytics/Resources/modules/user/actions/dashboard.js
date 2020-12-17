import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

/**
 * Opens the dashboard page of a user.
 */
export default (users, nodesRefresher, path) => ({
  name: 'dashboard',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-tachometer',
  label: trans('show_dashboard', {}, 'actions'),
  target: `${path}/dashboard`,
  displayed: hasPermission('show_dashboard', users[0])
})
