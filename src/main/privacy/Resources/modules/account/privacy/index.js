import {trans} from '#/main/app/intl/translation'
import {PrivacyMain} from '#/main/privacy/account/privacy/containers/main'
import {reducer} from '#/main/privacy/account/privacy/store/reducer'

export default {
  name: 'privacy',
  icon: 'fa fa-fw fa-user-shield',
  label: trans('privacy'),
  store: reducer,
  component: PrivacyMain,
  order: 3
}
