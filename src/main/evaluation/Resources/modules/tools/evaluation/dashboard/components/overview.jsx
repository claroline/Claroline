import React from 'react'

import {PageContent, PageSection} from '#/main/app/page'

import {StatusChart} from '#/main/evaluation/charts/status/components/chart'
import {ProgressionChart} from '#/main/evaluation/charts/progression/components/chart'
import {ScoreChart} from '#/main/evaluation/charts/score/components/chart'

const EvaluationDashboardOverview = () => {
  return (
    <PageContent>
      <PageSection size="full" className="my-4">
        <StatusChart />
      </PageSection>

      <PageSection size="full">
        <ProgressionChart />
      </PageSection>

      <PageSection size="full">
        <ScoreChart />
      </PageSection>
    </PageContent>
  )
}

export {
  EvaluationDashboardOverview
}
