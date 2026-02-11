import React from 'react'

import {ResourceDashboard} from '#/main/core/resource'

import {LessonDashboardOverview} from '#/plugin/lesson/resources/lesson/dashboard/components/overview'

const LessonDashboard = () =>
  <ResourceDashboard
    overviewPage={LessonDashboardOverview}
  />

export {
  LessonDashboard
}
