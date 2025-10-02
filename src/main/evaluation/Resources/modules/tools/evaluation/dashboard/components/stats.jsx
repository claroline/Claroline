import React from 'react'
import {useSelector} from 'react-redux'

import {PageContent, PageHeading, PageSection} from '#/main/app/page'
import {ProgressionChart} from '#/main/evaluation/chart/components/progression'
import {ScoreChart} from '#/main/evaluation/chart/components/score'
import {StatusChart} from '#/main/evaluation/chart/components/status'

import {selectors} from '#/main/evaluation/tools/evaluation/dashboard/store'
import {trans} from '#/main/app/intl'

const EvaluationDashboardStats = () => {
  const workspace = useSelector(selectors.workspace)
  const hasScore = useSelector(selectors.hasScore)
  const totalScore = useSelector(selectors.totalScore)
  const successScore = useSelector(selectors.successScore)

  return (
    <PageContent className="pt-4">
      <PageHeading title={trans('statistics')} className="visually-hidden" level={2} />

      <PageSection size="full">
        <div className="row">
          <div className="col-6">
            <StatusChart name={selectors.STORE_NAME+'.statuses'} url={['apiv2_workspace_evaluation_status', {id: workspace.id}]} />
          </div>
        </div>
      </PageSection>

      <PageSection size="full" className="mb-5">
        <ProgressionChart name={selectors.STORE_NAME+'.completion'} url={['apiv2_workspace_evaluation_completion', {id: workspace.id}]} />
      </PageSection>

      {hasScore &&
        <PageSection size="full" className="mb-5">
          <ScoreChart
            className="border-top pt-5"
            name={selectors.STORE_NAME+'.scores'}
            url={['apiv2_workspace_evaluation_scores', {id: workspace.id}]}
            totalScore={totalScore}
            successScore={successScore}
          />
        </PageSection>
      }
    </PageContent>
  )
}

export {
  EvaluationDashboardStats
}
