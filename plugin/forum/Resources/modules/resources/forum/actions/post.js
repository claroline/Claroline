import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

export default () => ({
  name: 'post',
  type: LINK_BUTTON,
  label: trans('create_subject', {}, 'forum'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/subjects/form'
})
