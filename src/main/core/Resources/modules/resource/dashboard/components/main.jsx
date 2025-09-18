import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Dashboard} from '#/main/app/dashboard'

import {ResourcePage} from '#/main/core/resource/components/page'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/main/core/resource/dashboard/store'
import {ResourceDashboardActivity} from '#/main/core/resource/dashboard/components/activity'
import {ResourceDashboardEvaluation} from '#/main/evaluation/resource/evaluation'
import {ResourceDashboardOverview} from '#/main/core/resource/dashboard/components/overview'
import {ResourceDashboardActions} from '#/main/core/resource/dashboard/components/actions'

const ResourceDashboard = (props) => {
  const dashboardPath = useSelector(selectors.path)
  const hasEvaluation = useSelector(resourceSelectors.hasEvaluation)

  return (
    <ResourcePage
      title={trans('dashboard')}
    >
      <Dashboard
        path={dashboardPath}
        overviewPage={props.overviewPage || ResourceDashboardOverview}
        statsPage={props.statsPage}
        activityPage={ResourceDashboardActivity}
        actionsPage={ResourceDashboardActions}
        pages={[
          {
            name: 'results',
            icon: 'fa fa-award',
            title: trans('evaluation'),
            component: ResourceDashboardEvaluation,
            disabled: !hasEvaluation
          }
        ].concat(props.pages || [])}
      />
    </ResourcePage>
  )
}

ResourceDashboard.propTypes = {
  overviewPage: T.elementType,
  statsPage: T.elementType,
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    icon: T.string.isRequired,
    title: T.string.isRequired,
    component: T.elementType,
    render: T.func,
    disabled: T.bool
  }))
}

export {
  ResourceDashboard
}
