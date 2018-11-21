import {trans} from '#/main/app/intl/translation'
import {isAuthenticated} from '#/main/app/security'

// TODO : implement

export default () => ({
  name: 'notifications',
  type: 'modal',
  icon: 'fa fa-fw fa-bell',
  label: trans('show-notifications', {}, 'actions'),
  displayed: isAuthenticated(),
  modal: []
})
