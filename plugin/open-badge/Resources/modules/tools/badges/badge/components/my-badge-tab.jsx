import React from 'react'

import {Routes} from '#/main/app/router'

import {MyBadges} from '#/plugin/open-badge/tools/badges/badge/components/my-badges'

const MyBadgeTab = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/my-badges',
        exact: true,
        component: MyBadges
      }
    ]}
  />

export {
  MyBadgeTab
}
