import {trans} from '#/main/app/intl/translation'

import {PrivacyMain} from '#/main/core/account/privacy/containers/main'

export default {
  name: 'privacy',
  icon: 'fa fa-fw fa-user-shield',
  label: trans('privacy'),
  component: PrivacyMain,
  order: 3
}
