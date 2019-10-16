import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Domain} from '#/main/core/administration/parameters/technical/components/domain'
import {Limits} from '#/main/core/administration/parameters/technical/components/limits'
import {Mailing} from '#/main/core/administration/parameters/technical/components/mailing'
import {Security} from '#/main/core/administration/parameters/technical/components/security'
import {Sessions} from '#/main/core/administration/parameters/technical/components/sessions'
import {Javascripts} from '#/main/core/administration/parameters/technical/components/javascripts'

const TechnicalTool = (props) =>
  <ToolPage
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/domain',      render: () => trans('internet')},
          {path: '/limits',      render: () => trans('limits')},
          {path: '/security',    render: () => trans('security')},
          {path: '/mailing',     render: () => trans('email')},
          {path: '/sessions',    render: () => trans('sessions')},
          {path: '/javascripts', render: () => trans('javascripts')}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/domain' }
      ]}
      routes={[
        {
          path: '/domain',
          component: Domain
        }, {
          path: '/limits',
          component: Limits
        }, {
          path: '/security',
          component: Security
        }, {
          path: '/mailing',
          component: Mailing
        }, {
          path: '/sessions',
          component: Sessions
        }, {
          path: '/javascripts',
          component: Javascripts
        }
      ]}
    />
  </ToolPage>

TechnicalTool.propTypes = {
  path: T.string.isRequired
}

export {
  TechnicalTool
}
