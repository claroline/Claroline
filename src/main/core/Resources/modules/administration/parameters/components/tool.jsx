import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Meta} from '#/main/core/administration/parameters/containers/meta'
import {Plugins} from '#/main/core/administration/parameters/containers/plugins'
import {Plugin} from '#/main/core/administration/parameters/containers/plugin'
import {Tool} from '#/main/core/tool'

const ParametersTool = (props) =>
  <Tool {...props}>
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
  </Tool>

ParametersTool.propTypes = {
  path: T.string,
  openPlugin: T.func.isRequired
}

export {
  ParametersTool
}
