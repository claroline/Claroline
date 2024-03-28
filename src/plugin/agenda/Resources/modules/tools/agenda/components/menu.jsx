import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants as toolConstants} from '#/main/core/tool/constants'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const AgendaMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'activity',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-wave-square',*/
        label: trans('activity'),
        target: `${props.path}/activity`,
        displayed: props.canShowActivity && (props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model'))
      }, {
        name: 'users',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-user',*/
        label: trans('users', {}, 'community'),
        target: `${props.path}/users`,
        displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')
      }, {
        name: 'groups',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-users',*/
        label: trans('groups', {}, 'community'),
        target: `${props.path}/groups`,
        displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')
      }, {
        name: 'pending',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-user-plus',*/
        label: trans('pending_registrations'),
        target: `${props.path}/pending`,
        displayed: props.contextType === toolConstants.TOOL_WORKSPACE && props.canEdit && get(props.workspace, 'registration.selfRegistration') && get(props.workspace, 'registration.validation')
      }, {
        name: 'teams',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-user-group',*/
        label: trans('teams', {}, 'community'),
        target: `${props.path}/teams`,
        displayed: props.contextType === toolConstants.TOOL_WORKSPACE
      }, {
        name: 'organizations',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-building',*/
        label: trans('organizations'),
        target: `${props.path}/organizations`,
        displayed: props.contextType === toolConstants.TOOL_DESKTOP/* && props.canEdit*/
      }, {
        name: 'roles',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-id-badge',*/
        label: trans('roles', {}, 'community'),
        target: `${props.path}/roles`,
        displayed: props.canEdit
      }, {
        name: 'profile',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-user-circle',*/
        label: trans('user_profile'),
        target: `${props.path}/parameters/profile`,
        displayed: props.contextType === toolConstants.TOOL_DESKTOP && props.canEdit
      }
    ]}
  />

CommunityMenu.propTypes = {
  contextType: T.string,
  path: T.string,
  workspace: T.object,
  canEdit: T.bool.isRequired,
  canShowActivity: T.bool.isRequired
}

export {
  CommunityMenu
}
