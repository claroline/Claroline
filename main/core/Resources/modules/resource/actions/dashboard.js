import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

/**
 * Opens the dashboard page of a resource.
 */
export default () => ({
  name: 'dashboard',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-tachometer',
  label: trans('dashboard', {}, 'tools'),
  target: '/dashboard'
})
