import {EvaluationDashboard} from '#/main/evaluation/analytics/resource/evaluation/containers/dashboard'

import {trans} from '#/main/app/intl/translation'

export default () => ({
  name: 'evaluation',
  order: 0,
  meta: {
    icon: 'fa fa-fw fa-award',
    label: trans('evaluation', {}, 'tools')
  },
  components: {
    tab: EvaluationDashboard
  }
})
