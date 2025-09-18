import React from 'react'

import {ResourceDashboard} from '#/main/core/resource'

import {QuizDashboardStats} from '#/plugin/exo/resources/quiz/dashboard/components/stats'

const QuizDashboard = () =>
  <ResourceDashboard
    statsPage={QuizDashboardStats}
  />

export {
  QuizDashboard
}
