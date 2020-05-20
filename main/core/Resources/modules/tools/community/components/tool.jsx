import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router'
import {constants as toolConstants} from '#/main/core/tool/constants'

import {User as UserType} from '#/main/core/user/prop-types'
import {Profile} from '#/main/core/user/profile/containers/main'
import {UserTab} from '#/main/core/tools/community/user/containers/tab'
import {GroupTab} from '#/main/core/tools/community/group/containers/tab'
import {RoleTab} from '#/main/core/tools/community/role/containers/tab'
import {ParametersTab} from '#/main/core/tools/community/parameters/containers/tab'
import {PendingTab} from '#/main/core/tools/community/pending/containers/tab'

const CommunityTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/users', disabled: props.contextType === toolConstants.TOOL_WORKSPACE && get(props.workspace, 'meta.model')},
      {from: '/', exact: true, to: '/roles', disabled: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')}
    ]}
    routes={[
      {
        path: '/users',
        component: UserTab,
        disabled: props.contextType === toolConstants.TOOL_WORKSPACE && get(props.workspace, 'meta.model')
      }, {
        path: '/groups',
        component: GroupTab,
        disabled:  props.contextType !== toolConstants.TOOL_WORKSPACE && get(props.workspace, 'meta.model')
      }, {
        path: '/roles',
        component: RoleTab,
        disabled: !props.canAdministrate || props.contextType !== toolConstants.TOOL_WORKSPACE
      }, {
        path: '/pending',
        component: PendingTab,
        disabled: !props.canAdministrate || props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'registration.selfRegistration') || !get(props.workspace, 'registration.validation')
      }, {
        path: '/parameters',
        component: ParametersTab,
        disabled: !props.canAdministrate || props.contextType !== toolConstants.TOOL_WORKSPACE
      }, {
        path: '/profile/:publicUrl',
        component: Profile,
        onEnter: (params = {}) => props.loadUser(params.publicUrl)
      }
    ]}
  />

CommunityTool.propTypes = {
  contextType: T.string,
  path: T.string.isRequired,
  currentUser: T.shape(UserType.propTypes),
  workspace: T.object,
  loadUser: T.func.isRequired,
  canAdministrate: T.bool.isRequired
}

export {
  CommunityTool
}
