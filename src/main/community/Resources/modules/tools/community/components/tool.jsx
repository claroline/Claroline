import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Tool} from '#/main/core/tool'
import {constants as toolConstants} from '#/main/core/tool/constants'

import {ActivityMain} from '#/main/community/tools/community/activity/containers/main'
import {UserMain} from '#/main/community/tools/community/user/containers/main'
import {GroupMain} from '#/main/community/tools/community/group/containers/main'
import {RoleMain} from '#/main/community/tools/community/role/containers/main'
import {PendingMain} from '#/main/community/tools/community/pending/containers/main'
import {OrganizationMain} from '#/main/community/tools/community/organization/containers/main'
import {TeamMain} from '#/main/community/tools/community/team/containers/main'

import {MODAL_USER_DISABLE_INACTIVE} from '#/main/community/tools/community/user/modals/disable-inactive'
import {CommunityEditor} from '#/main/community/tools/community/editor/containers/main'

const CommunityTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-community-tool']}
    redirect={[
      {from: '/', exact: true, to: '/users', disabled: props.contextType === toolConstants.TOOL_WORKSPACE && get(props.contextData, 'meta.model')},
      {from: '/', exact: true, to: '/roles', disabled: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.contextData, 'meta.model')}
    ]}
    menu={[
      {
        name: 'activity',
        type: LINK_BUTTON,
        label: trans('activity'),
        target: `${props.path}/activity`,
        displayed: props.canShowActivity && (props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.contextData, 'meta.model'))
      }, {
        name: 'users',
        type: LINK_BUTTON,
        label: trans('users', {}, 'community'),
        target: `${props.path}/users`,
        displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.contextData, 'meta.model')
      }, {
        name: 'groups',
        type: LINK_BUTTON,
        label: trans('groups', {}, 'community'),
        target: `${props.path}/groups`,
        displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.contextData, 'meta.model')
      }, {
        name: 'pending',
        type: LINK_BUTTON,
        label: trans('pending_registrations'),
        target: `${props.path}/pending`,
        displayed: props.contextType === toolConstants.TOOL_WORKSPACE && props.canEdit && get(props.contextData, 'registration.validation')
      }, {
        name: 'teams',
        type: LINK_BUTTON,
        label: trans('teams', {}, 'community'),
        target: `${props.path}/teams`,
        displayed: props.contextType === toolConstants.TOOL_WORKSPACE
      }, {
        name: 'organizations',
        type: LINK_BUTTON,
        label: trans('organizations'),
        target: `${props.path}/organizations`,
        displayed: props.contextType === toolConstants.TOOL_DESKTOP/* && props.canEdit*/
      }, {
        name: 'roles',
        type: LINK_BUTTON,
        label: trans('roles', {}, 'community'),
        target: `${props.path}/roles`,
        displayed: props.canEdit
      }
    ]}
    pages={[
      {
        path: '/activity',
        component: ActivityMain,
        disabled: !props.canShowActivity || (props.contextType === toolConstants.TOOL_WORKSPACE && get(props.contextData, 'meta.model'))
      }, {
        path: '/users',
        component: UserMain,
        disabled: props.contextType === toolConstants.TOOL_WORKSPACE && get(props.contextData, 'meta.model')
      }, {
        path: '/groups',
        component: GroupMain,
        disabled:  props.contextType === toolConstants.TOOL_WORKSPACE && get(props.contextData, 'meta.model')
      }, {
        path: '/roles',
        component: RoleMain,
        disabled: !props.canEdit
      }, {
        path: '/organizations',
        component: OrganizationMain,
        disabled: props.contextType !== toolConstants.TOOL_DESKTOP/* || !props.canEdit*/
      }, {
        path: '/teams',
        component: TeamMain,
        disabled: props.contextType === toolConstants.TOOL_DESKTOP
      }, {
        path: '/pending',
        component: PendingMain,
        disabled: !props.canEdit || props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.contextData, 'registration.selfRegistration') || !get(props.contextData, 'registration.validation')
      }
    ]}
    actions={[
      {
        name: 'disable-inactive',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-user-clock',
        label: trans('disable_inactive_users', {}, 'community'),
        modal: [MODAL_USER_DISABLE_INACTIVE],
        displayed: 'desktop' === props.contextType && props.canAdministrate,
        dangerous: true
      }
    ]}
    editor={CommunityEditor}
  />

CommunityTool.propTypes = {
  path: T.string.isRequired,
  contextType: T.string,
  contextData: T.object,
  workspace: T.object,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  canShowActivity: T.bool.isRequired
}

export {
  CommunityTool
}
