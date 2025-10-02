import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {PageContent, PageHeading, PageSection} from '#/main/app/page'

import {ActivityChart} from '#/main/evaluation/chart/components/activity'
import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store'

import {selectors} from '#/main/evaluation/sequence/dashboard/store'
import {SequenceDashboardMetrics} from '#/main/evaluation/sequence/dashboard/overview/components/metrics'
import {SequenceDashboardInfo} from '#/main/evaluation/sequence/dashboard/overview/components/info'
import {SequenceDashboardContent} from '#/main/evaluation/sequence/dashboard/overview/components/content'

const SequenceDashboardOverview = () => {
  const sequence = useSelector(sequenceSelectors.sequence)

  return (
    <PageContent>
      <PageHeading title={trans('overview')} className="visually-hidden" level={2} />
      <PageSection className="my-4" size="full" title={trans('general')} level={3} showTitle={false} flush={true}>
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
            className="col-md-8 px-4"
            name={selectors.STORE_NAME+'.activity'}
            activityUrl={(activityType) => ['apiv2_sequence_activity', {id: sequence.id, activityType: activityType}]}
            logUrl={['apiv2_sequence_functional_logs', {id: sequence.id}]}
            viewUrl={['apiv2_sequence_views', {id: sequence.id}]}
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
