import {trans} from '#/main/app/intl/translation'

import {ProfileMain} from '#/main/core/account/profile/containers/main'

export default {
  name: 'profile',
  icon: 'fa fa-fw fa-id-card',
  label: trans('user_profile'),
  component: ProfileMain
}
