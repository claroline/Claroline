import React from 'react'
import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

// app sections
import {OverviewTab} from '#/main/core/administration/analytics/components/overview/tab.jsx'
import {AudienceTab} from '#/main/core/administration/analytics/components/audience/tab.jsx'
import {ResourcesTab} from '#/main/core/administration/analytics/components/resources/tab.jsx'
import {WidgetsTab} from '#/main/core/administration/analytics/components/widgets/tab.jsx'
import {TopActionsTab} from '#/main/core/administration/analytics/components/top-actions/tab.jsx'

const Tool = () =>
  <TabbedPageContainer
    title={trans('admin_analytics')}
    redirect={[
      {from: '/', exact: true, to: '/overview'}
    ]}
    
    tabs={[
      {
        icon: 'fa fa-dashboard',
        title: trans('analytics_home'),
        path: '/overview',
        actions: null,
        content: OverviewTab
      }, {
        icon: 'fa fa-line-chart',
        title: trans('user_visit'),
        path: '/audience',
        actions: null,
        content: AudienceTab
      }, {
        icon: 'fa fa-folder',
        title: trans('analytics_resources'),
        path: '/resources',
        actions: null,
        content: ResourcesTab
      }, {
        icon: 'fa fa-list-alt',
        title: trans('widgets'),
        path: '/widgets',
        actions: null,
        content: WidgetsTab
      }, {
        icon: 'fa fa-sort-amount-desc',
        title: trans('analytics_top'),
        path: '/top',
        actions: null,
        content: TopActionsTab
      }
    ]}
  />

export {
  Tool as AnalyticsTool
}
