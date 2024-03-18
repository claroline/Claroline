import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Meta} from '#/main/core/administration/parameters/containers/meta'
import {Plugins} from '#/main/core/administration/parameters/containers/plugins'
import {Plugin} from '#/main/core/administration/parameters/containers/plugin'

const ParametersTool = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/',
        exact: true,
        component: Meta
      }, {
        path: '/plugins',
        component: Plugins,
        exact: true
      }, {
        path: '/plugins/:id',
        onEnter: (params = {}) => props.openPlugin(params.id),
        component: Plugin
      }
    ]}
  />

ParametersTool.propTypes = {
  path: T.string,
  openPlugin: T.func.isRequired
}

export {
  ParametersTool
}
