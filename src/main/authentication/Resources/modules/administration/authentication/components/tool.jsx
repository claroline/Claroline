import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Tool} from '#/main/core/tool'

import {AuthenticationParameters} from '#/main/authentication/administration/authentication/containers/parameters'
import {AuthenticationIps} from '#/main/authentication/administration/authentication/containers/ips'
import {AuthenticationTokens} from '#/main/authentication/administration/authentication/containers/tokens'

const AuthenticationTool = (props) =>
  <Tool
    {...props}
    menu={[
      {
        name: 'parameters',
        type: LINK_BUTTON,
        label: trans('parameters'),
        target: `${props.path}/`,
        exact: true
      }, {
        name: 'ips',
        type: LINK_BUTTON,
        label: trans('ips', {}, 'integration'),
        target: `${props.path}/ips`
      }, {
        name: 'tokens',
        type: LINK_BUTTON,
        label: trans('tokens', {}, 'integration'),
        target: `${props.path}/tokens`
      }
    ]}
    pages={[
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
      }
    ]}
  />

AuthenticationTool.propTypes = {
  path: T.string.isRequired
}

export {
  AuthenticationTool
}
