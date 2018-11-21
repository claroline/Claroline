import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

export default () => ({
  name: 'create-announce',
  type: LINK_BUTTON,
  label: trans('create-announce', {}, 'actions'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/add'
})
