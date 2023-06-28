import { trans } from '#/main/app/intl/translation'
import { PrivacyMain } from '#/main/privacy/account/privacy/components/main'

export default {
  name: 'privacy',
  icon: 'fa fa-fw fa-user-shield',
  label: trans('privacy'),
  component: PrivacyMain,
  order: 3
}
