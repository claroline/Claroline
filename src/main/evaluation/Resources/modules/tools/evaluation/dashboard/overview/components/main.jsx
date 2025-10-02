import React from 'react'
import {useSelector} from 'react-redux'

import {PageContent, PageHeading, PageSection} from '#/main/app/page'
import {ActivityChart} from '#/main/evaluation/chart/components/activity'

import {selectors} from '#/main/evaluation/tools/evaluation/dashboard/store'
import {EvaluationDashboardMetrics} from '#/main/evaluation/tools/evaluation/dashboard/overview/components/metrics'
import {EvaluationDashboardInfo} from '#/main/evaluation/tools/evaluation/dashboard/overview/components/info'
import {trans} from '#/main/app/intl'

const EvaluationDashboardOverview = () => {
  const workspace = useSelector(selectors.workspace)

  return (
    <PageContent>
      <PageHeading title={trans('overview')} className="visually-hidden" level={2} />
      <PageSection className="my-4" size="lg" title={trans('general')} level={3} showTitle={false}>
        <EvaluationDashboardInfo />
      </PageSection>

      <PageSection size="full" className="mb-4">
        <div className="border-top border-bottom" role="presentation">
          <EvaluationDashboardMetrics />
        </div>
      </PageSection>

      <PageSection size="full" className="my-5">
        <div className="row mx-n4">
          <ActivityChart
            className="col-md-8 px-4"
            name={selectors.STORE_NAME+'.activity'}
            activityUrl={(activityType) => ['apiv2_workspace_activity', {id: workspace.id, activityType: activityType}]}
            logUrl={['apiv2_workspace_functional_logs', {id: workspace.id}]}
            viewUrl={['apiv2_workspace_views', {id: workspace.id}]}
          />
        </div>
      </PageSection>
    </PageContent>
  )
}

export {
  EvaluationDashboardOverview
}
