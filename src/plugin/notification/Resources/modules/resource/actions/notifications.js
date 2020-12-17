import {trans} from '#/main/app/intl/translation'

// TODO : implement

export default (resourceNodes, nodesRefresher, path, currentUser) => ({
  name: 'notifications',
  type: 'modal',
  icon: 'fa fa-fw fa-bell',
  label: trans('show-notifications', {}, 'actions'),
  displayed: !!currentUser,
  modal: []
})
