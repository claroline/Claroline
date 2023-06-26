import { trans } from '#/main/app/intl/translation'
import { PrivacyTool } from '#/main/privacy/account/privacy/containers/tool'
import { reducer } from '#/main/privacy/account/privacy/store/reducer'

export default {
  name: 'privacy',
  icon: 'fa fa-fw fa-user-shield',
  label: trans('privacy'),
  component: PrivacyTool,
  order: 3,
  selector: reducer
}
