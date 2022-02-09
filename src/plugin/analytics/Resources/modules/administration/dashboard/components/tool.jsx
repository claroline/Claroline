import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {DashboardOverview} from '#/plugin/analytics/administration/dashboard/components/overview'
import {DashboardActivity} from '#/plugin/analytics/administration/dashboard/components/activity'
import {DashboardContent} from '#/plugin/analytics/administration/dashboard/components/content'
import {DashboardCommunity} from '#/plugin/analytics/administration/dashboard/components/community'

const DashboardTool = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => {
          const Overview = (
            <DashboardOverview count={props.count} />
          )

          return Overview
        }
      }, {
        path: '/activity',
        render: () => {
          const Activity = (
            <DashboardActivity count={props.count} />
          )

          return Activity
        }
      }, {
        path: '/content',
        render: () => {
          const Content = (
            <DashboardContent count={props.count} />
          )

          return Content
        }
      }, {
        path: '/community',
        render: () => {
          const Community = (
            <DashboardCommunity count={props.count} />
          )

          return Community
        }
      }
    ]}
  />

DashboardTool.propTypes = {
  path: T.string.isRequired,
  count: T.shape({
    workspaces: T.number,
    resources: T.number,
    storage: T.number,
    connections: T.shape({
      count: T.number,
      avgTime: T.number
    }),
    users: T.number,
    roles: T.number,
    groups: T.number,
    organizations: T.number
  }).isRequired
}

export {
  DashboardTool
}
