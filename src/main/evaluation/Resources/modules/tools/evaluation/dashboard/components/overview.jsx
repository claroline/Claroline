import React from 'react'

import {PageContent, PageSection} from '#/main/app/page'

import {ProgressionChart} from '#/main/evaluation/chart/components/progression'
import {ScoreChart} from '#/main/evaluation/chart/components/score'
import {StatusChart} from '#/main/evaluation/chart/components/status'

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
