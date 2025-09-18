import React from 'react'

import {trans} from '#/main/app/intl'
import {selectors as toolSelectors, ToolDashboard} from '#/main/core/tool'

import {EvaluationDashboardOverview} from '#/main/evaluation/tools/evaluation/dashboard/components/overview'
import {EvaluationDashboardEvaluations} from '#/main/evaluation/tools/evaluation/dashboard/components/evaluations'
import {useSelector} from 'react-redux'

const EvaluationDashboard = () => {
  const contextType = useSelector(toolSelectors.contextType)

  return (
    <ToolDashboard
      overviewPage={'workspace' === contextType ? EvaluationDashboardOverview : undefined}
      pages={[
        {
          name: 'results',
          icon: 'fa fa-award',
          title: trans('evaluation'),
          component: EvaluationDashboardEvaluations
        }
      ]}
    />
  )
}

export {
  EvaluationDashboard
}
