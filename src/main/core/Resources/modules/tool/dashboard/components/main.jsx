import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Dashboard} from '#/main/app/dashboard'

import {ToolPage} from '#/main/core/tool/components/page'
import {selectors} from '#/main/core/tool/dashboard/store'
import {ToolDashboardActions} from '#/main/core/tool/dashboard/components/actions'

const ToolDashboard = (props) => {
  const dashboardPath = useSelector(selectors.path)

  return (
    <ToolPage title={trans('dashboard')}>
      <Dashboard
        path={dashboardPath}
        pages={props.pages}
        overviewPage={props.overviewPage}
        statsPage={props.statsPage}
        activityPage={props.activityPage}
        actionsPage={ToolDashboardActions}
      />
    </ToolPage>
  )
}

ToolDashboard.propTypes = {
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    icon: T.string.isRequired,
    title: T.string.isRequired,
    component: T.elementType,
    render: T.func,
    disabled: T.bool
  })),

  // standard pages
  overviewPage: T.elementType,
  activityPage: T.elementType,
  statsPage: T.elementType
}

export {
  ToolDashboard
}
