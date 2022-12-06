import {trans} from '#/main/app/intl/translation'

import {ProfileMain} from '#/main/community/account/profile/containers/main'

export default {
  name: 'profile',
  icon: 'fa fa-fw fa-user-circle',
  label: trans('user_profile'),
  component: ProfileMain,
  order: 1
}
