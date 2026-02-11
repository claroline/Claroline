import React from 'react'

import {ToolDashboard} from '#/main/core/tool'
import {HomeDashboardOverview} from '#/plugin/home/tools/home/dashboard/components/overview'
const HomeDashboard = () => {
  return (
    <ToolDashboard
      overviewPage={HomeDashboardOverview}
    />
  )
}

export {
  HomeDashboard
}
