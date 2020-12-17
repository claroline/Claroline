import {Requirements} from '#/plugin/analytics/analytics/resource/requirements/containers/requirements'

import {trans} from '#/main/app/intl/translation'

export default () => ({
  component: Requirements,
  icon: 'fa fa-fw fa-clipboard-list',
  name: 'requirements',
  label: trans('evaluation_requirements', {}, 'analytics'),
  path: 'requirements',
  displayed: true
})
