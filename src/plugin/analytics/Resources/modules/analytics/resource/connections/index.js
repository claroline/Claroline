import {Connections} from '#/plugin/analytics/analytics/resource/connections/containers/connections'

import {trans} from '#/main/app/intl/translation'

export default () => ({
  component: Connections,
  icon: 'fa fa-fw fa-clock',
  name: 'connections',
  label: trans('connection_time'),
  path: '',
  displayed: true
})
