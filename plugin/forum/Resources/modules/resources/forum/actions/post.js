import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'post',
  type: LINK_BUTTON,
  label: trans('create_subject', {}, 'forum'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/subjects/form'
})

export {
  action
}
