import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {selectors as toolSelectors, ToolDashboard} from '#/main/core/tool'

import {EvaluationDashboardOverview} from '#/main/evaluation/tools/evaluation/dashboard/overview/components/main'
import {EvaluationDashboardEvaluation} from '#/main/evaluation/tools/evaluation/dashboard/evaluation/components/main'
import {EvaluationDashboardStats} from '#/main/evaluation/tools/evaluation/dashboard/components/stats'

const EvaluationDashboard = () => {
  const contextType = useSelector(toolSelectors.contextType)

  return (
    <ToolDashboard
      overviewPage={'workspace' === contextType ? EvaluationDashboardOverview : undefined}
      statsPage={'workspace' === contextType ? EvaluationDashboardStats : undefined}
      pages={[
        {
          name: 'results',
          icon: 'fa fa-award',
          title: trans('evaluation'),
          component: EvaluationDashboardEvaluation
        }
      ]}
    />
  )
}

export {
  EvaluationDashboard
}
