import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {PageContent, PageSection} from '#/main/app/page'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {ResourceDashboardMetrics} from '#/main/core/resource/dashboard/overview/components/metrics'
import {ResourceDashboardInfo} from '#/main/core/resource/dashboard/overview/components/info'
import {ActivityChart} from '#/main/evaluation/chart/components/activity'

const ResourceDashboardOverview = ({children}) => {
  const resource = useSelector(resourceSelectors.resourceNode)

  return (
    <PageContent>
      <PageSection className="my-4" size="lg">
        <ResourceDashboardInfo />
      </PageSection>

      <PageSection size="full" className="mb-4">
        <div className="border-top border-bottom" role="presentation">
          <ResourceDashboardMetrics />
        </div>
      </PageSection>

      <PageSection size="full" className="my-5">
        <div className="row mx-n4">
          <ActivityChart
            className="col-md-8 px-4"
            activityUrl={(activityType) => ['apiv2_resource_activity', {id: resource.id, activityType: activityType}]}
            logUrl={['apiv2_resource_functional_logs', {id: resource.id}]}
            viewUrl={['apiv2_resource_views', {id: resource.id}]}
          />
        </div>
      </PageSection>

      {children}
    </PageContent>
  )
}

ResourceDashboardOverview.propTypes = {
  children: T.any
}

export {
  ResourceDashboardOverview
}
