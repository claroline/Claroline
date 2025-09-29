import React from 'react'
import {useSelector} from 'react-redux'

import {PageContent, PageSection} from '#/main/app/page'

import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store'

import {SequenceDashboardMetrics} from '#/main/evaluation/sequence/dashboard/overview/components/metrics'
import {SequenceDashboardInfo} from '#/main/evaluation/sequence/dashboard/overview/components/info'
import {SequenceDashboardContent} from '#/main/evaluation/sequence/dashboard/overview/components/content'
import {ActivityChart} from '#/main/evaluation/chart/components/activity'

const SequenceDashboardOverview = () => {
  const sequence = useSelector(sequenceSelectors.sequence)

  return (
    <PageContent>
      <PageSection className="my-4" size="lg">
        <SequenceDashboardInfo />
      </PageSection>

      <PageSection size="full" className="mb-4">
        <div className="border-top border-bottom" role="presentation">
          <SequenceDashboardMetrics />
        </div>
      </PageSection>

      <PageSection size="full" className="my-5">
        <div className="row mx-n4">
          <ActivityChart
            activityUrl={(activityType) => ['apiv2_sequence_activity', {id: sequence.id, activityType: activityType}]}
            logUrl={['apiv2_sequence_functional_logs', {id: sequence.id}]}
            viewUrl={['apiv2_sequence_views', {id: sequence.id}]}
            className="col-md-8 px-4"
          />

          <div className="col-md-4 border-start px-4">
            <SequenceDashboardContent />
          </div>
        </div>
      </PageSection>
    </PageContent>
  )
}

export {
  SequenceDashboardOverview
}
