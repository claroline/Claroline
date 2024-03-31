import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {Meta} from '#/main/core/administration/parameters/containers/meta'
import {Plugins} from '#/main/core/administration/parameters/containers/plugins'
import {Plugin} from '#/main/core/administration/parameters/containers/plugin'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const ParametersTool = (props) =>
  <Tool
    {...props}
    menu={[
      {
        name: 'general',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-circle-info',
        label: trans('general'),
        target: props.path+'/',
        exact: true
      }, {
        name: 'plugins',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-drafting-compass',
        label: trans('plugins'),
        target: props.path+'/plugins'
      }
    ]}
    pages={[
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
  path: T.string.isRequired,
  openPlugin: T.func.isRequired
}

export {
  ParametersTool
}
