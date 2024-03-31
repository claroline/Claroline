import React from 'react'

import {Tool} from '#/main/core/tool'

import {LogsSecurity} from '#/main/log/administration/logs/components/security'
import {LogsMessage} from '#/main/log/administration/logs/components/message'
import {LogsFunctional} from '#/main/log/administration/logs/components/functional'
import {LogsOperational} from '#/main/log/administration/logs/components/operational'
import {LogsTypes} from '#/main/log/administration/logs/components/types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const LogsTool = (props) =>
  <Tool
    {...props}
    redirect={[
      { from: '/', exact: true, to : '/security' }
    ]}
    menu={[
      {
        name: 'logs',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-shield',
        label: trans('security', {}, 'log'),
        target: props.path + '/security'
      }, {
        name: 'message',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-envelope',
        label: trans('message', {}, 'log'),
        target: props.path + '/message'
      }, {
        name: 'functional',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-users-viewfinder',
        label: trans('functional', {}, 'log'),
        target: props.path + '/functional'
      }, {
        name: 'operational',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-pencil',
        label: trans('operational', {}, 'log'),
        target: props.path + '/operational'
      }, {
        name: 'types',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-cog',
        label: trans('parameters'),
        target: props.path + '/parameters'
      }
    ]}
    pages={[
      {
        path: '/security',
        component: LogsSecurity
      }, {
        path: '/message',
        component: LogsMessage
      }, {
        path: '/functional',
        component: LogsFunctional
      }, {
        path: '/operational',
        component: LogsOperational
      }, {
        path: '/parameters',
        component: LogsTypes
      }
    ]}
  />

export {
  LogsTool
}
