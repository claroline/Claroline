import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'add-entry',
  type: LINK_BUTTON,
  label: trans('add-entry', {}, 'actions'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/entry/form'
})

export {
  action
}
