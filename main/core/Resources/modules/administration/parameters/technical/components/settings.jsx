import React from 'react'

import {Routes} from '#/main/app/router'

import {Authentication} from '#/main/core/administration/parameters/technical/components/authentication'
import {Domain} from '#/main/core/administration/parameters/technical/components/domain'
import {Limits} from '#/main/core/administration/parameters/technical/components/limits'
import {Mailing} from '#/main/core/administration/parameters/technical/components/mailing'
import {Maintenance} from '#/main/core/administration/parameters/technical/components/maintenance'
import {Pdf} from '#/main/core/administration/parameters/technical/components/pdf'
import {Security} from '#/main/core/administration/parameters/technical/components/security'
import {Synchronization} from '#/main/core/administration/parameters/technical/components/synchronization'
import {Token} from '#/main/core/administration/parameters/technical/components/token'
import {Sessions} from '#/main/core/administration/parameters/technical/components/sessions'
import {Javascripts} from '#/main/core/administration/parameters/technical/components/javascripts'

const Settings = () =>
  <Routes
    redirect={[
      {from: '/', exact: true, to: '/domain' }
    ]}
    routes={[
      {
        path: '/authentication',
        exact: true,
        component: Authentication
      },
      {
        path: '/sessions',
        exact: true,
        component: Sessions
      },
      {
        path: '/domain',
        exact: true,
        component: Domain
      }, {
        path: '/limits',
        exact: true,
        component: Limits
      }, {
        path: '/mailing',
        exact: true,
        component: Mailing
      }, {
        path: '/maintenance',
        exact: true,
        component: Maintenance
      }, {
        path: '/pdf',
        exact: true,
        component: Pdf
      }, {
        path: '/security',
        exact: true,
        component: Security
      }, {
        path: '/javascripts',
        exact: true,
        component: Javascripts
      }, {
        path: '/synchronization',
        exact: true,
        component: Synchronization
      }, {
        path: '/token',
        exact: true,
        component: Token
      }
    ]}
  />

export {
  Settings
}
