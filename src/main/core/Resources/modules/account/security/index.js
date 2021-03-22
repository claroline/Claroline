import {trans} from '#/main/app/intl/translation'

import {SecurityMain} from '#/main/core/account/security/containers/main'

export default {
  name: 'security',
  icon: 'fa fa-fw fa-shield',
  label: trans('security'),
  component: SecurityMain
}
