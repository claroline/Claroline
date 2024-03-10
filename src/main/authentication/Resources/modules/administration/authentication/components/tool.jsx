import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {AuthenticationParameters} from '#/main/authentication/administration/authentication/containers/parameters'
import {AuthenticationIps} from '#/main/authentication/administration/authentication/containers/ips'
import {AuthenticationTokens} from '#/main/authentication/administration/authentication/containers/tokens'

const AuthenticationTool = (props) =>
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

AuthenticationTool.propTypes = {
  path: T.string.isRequired
}

export {
  AuthenticationTool
}
