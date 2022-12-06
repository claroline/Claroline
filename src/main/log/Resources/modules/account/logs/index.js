import {trans} from '#/main/app/intl/translation'

import {LogsMain} from '#/main/log/account/logs/containers/main'

export default {
  name: 'logs',
  icon: 'fa fa-fw fa-shoe-prints',
  label: trans('logs', {}, 'tools'),
  component: LogsMain
}
