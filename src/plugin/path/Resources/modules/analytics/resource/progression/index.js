import {Progression} from '#/plugin/path/analytics/resource/progression/containers/progression'

import {trans} from '#/main/app/intl/translation'

export default (resourceNode) => ({
  component: Progression,
  icon: 'fa fa-fw fa-tasks',
  name: 'path_progression',
  label: trans('progression'),
  path: '/progression',
  displayed: 'innova_path' === resourceNode.meta.type
})
