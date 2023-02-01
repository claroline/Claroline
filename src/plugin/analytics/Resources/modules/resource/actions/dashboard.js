import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

/**
 * Opens the dashboard page of a resource.
 */
export default (resourceNodes, nodesRefresher, path) => ({
  name: 'dashboard',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-wave-square',
  label: trans('show_activity', {}, 'actions'),
  target: `${path}/${resourceNodes[0].slug}/dashboard`
})
