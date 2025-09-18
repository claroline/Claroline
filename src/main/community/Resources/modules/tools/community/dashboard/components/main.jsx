import React from 'react'

import {ToolDashboard} from '#/main/core/tool'

import {DashboardActivity} from '#/main/community/tools/community/dashboard/components/activity'

const CommunityDashboard = () =>
  <ToolDashboard
    activityPage={DashboardActivity}
  />

export {
  CommunityDashboard
}
