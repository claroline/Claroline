import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {AuthenticationParameters} from '#/main/authentication/administration/authentication/containers/parameters'
import {AuthenticationIps} from '#/main/authentication/administration/authentication/containers/ips'
import {AuthenticationTokens} from '#/main/authentication/administration/authentication/containers/tokens'
import {Tool} from '#/main/core/tool'

const AuthenticationTool = (props) =>
  <Tool {...props}>
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          exact: true,
          component: AuthenticationParameters
        }, {
          path: '/ips',
          component: AuthenticationIps
        }, {
          path: '/tokens',
          component: AuthenticationTokens
        },
      ]}
    />
  </Tool>

AuthenticationTool.propTypes = {
  path: T.string.isRequired
}

export {
  AuthenticationTool
}
