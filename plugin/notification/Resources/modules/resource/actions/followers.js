import {trans} from '#/main/app/intl/translation'

const action = () => ({
  name: 'followers',
  type: 'modal',
  icon: 'fa fa-fw fa-users',
  label: trans('show-followers', {}, 'actions'),
  modal: []
})

export {
  action
}
