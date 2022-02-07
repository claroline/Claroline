import {Paths} from '#/plugin/path/analytics/workspace/path/containers/paths'

import {trans} from '#/main/app/intl/translation'

export default () => ({
  name: 'paths',
  meta: {
    icon: 'fa fa-fw fa-wave-square',
    label: trans('paths_tracking')
  },
  components: {
    tab: Paths
  }
})
