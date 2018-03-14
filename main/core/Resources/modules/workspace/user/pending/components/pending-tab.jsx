import React from 'react'
import {Routes} from '#/main/core/router'
import {Pendings}   from '#/main/core/workspace/user/pending/components/pendings.jsx'

const PendingTab = () =>
  <Routes
    routes={[
      {
        path: '/pendings',
        exact: true,
        component: Pendings
      }
    ]}
  />

export {
  PendingTab
}
