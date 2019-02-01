import React from 'react'

import {Routes} from '#/main/app/router'

import {MyBadges} from '#/plugin/open-badge/tools/badges/badge/components/my-badges'

const MyBadgeTab = () =>
  <Routes
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
