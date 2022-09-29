import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Plugins} from '#/main/core/administration/plugins/containers/plugins'
import {Plugin} from '#/main/core/administration/plugins/containers/plugin'

const PluginsTool = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/',
        exact: true,
        component: Plugins
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.open(params.id),
        component: Plugin
      }
    ]}
  />

PluginsTool.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired
}

export {
  PluginsTool
}
