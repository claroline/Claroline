import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const LogsMenu = (props) =>
  <ToolMenu
    actions={[
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
  />

LogsMenu.propTypes = {
  path: T.string
}

export {
  LogsMenu
}
