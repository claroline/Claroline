import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ToolMenu} from '#/main/core/tool/containers/menu'

const WorkspacesMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'registered',
        type: LINK_BUTTON,
        label: trans('my_workspaces_menu', {}, 'workspace'),
        target: props.path+'/registered',
        displayed: props.authenticated
      }, {
        name: 'public',
        type: LINK_BUTTON,
        label: trans('public_workspaces_menu', {}, 'workspace'),
        target: props.path+'/public'
      }, {
        name: 'managed',
        type: LINK_BUTTON,
        label: trans('managed_workspaces_menu', {}, 'workspace'),
        target: props.path+'/managed',
        displayed: props.authenticated
      }, {
        name: 'model',
        type: LINK_BUTTON,
        label: trans('models'),
        target: props.path+'/model',
        displayed: props.canCreate
      }, {
        name: 'archive',
        type: LINK_BUTTON,
        label: trans('archives'),
        target: props.path+'/archived',
        displayed: props.canArchive
      }
    ]}
  />

WorkspacesMenu.propTypes = {
  path: T.string,
  authenticated: T.bool.isRequired,
  canCreate: T.bool.isRequired,
  canArchive: T.bool.isRequired
}

export {
  WorkspacesMenu
}
