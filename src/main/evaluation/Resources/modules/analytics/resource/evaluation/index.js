import {EvaluationDashboard} from '#/main/evaluation/analytics/resource/evaluation/containers/dashboard'

import {trans} from '#/main/app/intl/translation'

export default () => ({
  component: EvaluationDashboard,
  icon: 'fa fa-fw fa-award',
  name: 'evaluation',
  label: trans('evaluation', {}, 'tools'),
  path: '/evaluation',
  displayed: true
})
