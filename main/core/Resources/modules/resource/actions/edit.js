import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  primary: true,
  target: '/edit'
})

export {
  action
}
