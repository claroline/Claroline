import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'create-announce',
  type: LINK_BUTTON,
  label: trans('add_announce', {}, 'announcement'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/add'
})

export {
  action
}
