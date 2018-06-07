import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = () => ({
  name: 'notifications',
  type: 'modal',
  icon: 'fa fa-fw fa-bell',
  label: trans('show-notifications', {}, 'actions'),
  displayed: isAuthenticated(),
  modal: []
})

export {
  action
}
