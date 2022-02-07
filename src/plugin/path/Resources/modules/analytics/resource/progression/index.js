import {Progression} from '#/plugin/path/analytics/resource/progression/containers/progression'

import {trans} from '#/main/app/intl/translation'

export default (resourceNode) => ({
  name: 'path_progression',
  displayed: 'innova_path' === resourceNode.meta.type,
  meta: {
    label: trans('progression'),
    icon: 'fa fa-fw fa-tasks'
  },
  components: {
    tab: Progression
  }
})
