import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Routes} from '#/main/app/router'
import {constants as toolConstants} from '#/main/core/tool/constants'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {Profile} from '#/main/community/profile/containers/main'
import {UserMain} from '#/main/community/tools/community/user/containers/main'
import {GroupMain} from '#/main/community/tools/community/group/containers/main'
import {RoleMain} from '#/main/community/tools/community/role/containers/main'
import {PendingMain} from '#/main/community/tools/community/pending/containers/main'
import {ProfileMain} from '#/main/community/tools/community/profile/containers/main'
import {OrganizationMain} from '#/main/community/tools/community/organization/containers/main'

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
        component: UserMain,
        disabled: props.contextType === toolConstants.TOOL_WORKSPACE && get(props.workspace, 'meta.model')
      }, {
        path: '/groups',
        component: GroupMain,
        disabled:  props.contextType === toolConstants.TOOL_WORKSPACE && get(props.workspace, 'meta.model')
      }, {
        path: '/roles',
        component: RoleMain,
        disabled: !props.canAdministrate
      }, {
        path: '/organizations',
        component: OrganizationMain,
        disabled: props.contextType !== toolConstants.TOOL_DESKTOP || !props.canAdministrate
      }, {
        path: '/pending',
        component: PendingMain,
        disabled: !props.canAdministrate || props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'registration.selfRegistration') || !get(props.workspace, 'registration.validation')
      }, /*{
        path: '/parameters',
        component: ParametersMain,
        disabled: !props.canAdministrate,
        exact: true
      }, */{
        path: '/parameters/profile',
        component: ProfileMain,
        disabled: props.contextType !== toolConstants.TOOL_DESKTOP || !props.canAdministrate
      }, {
        path: '/profile/:username',
        render(routerProps) {
          return (
            <Profile
              path={props.path + '/profile/' + routerProps.match.params.username}
              showBreadcrumb={showToolBreadcrumb(props.contextType, props.contextData)}
              breadcrumb={getToolBreadcrumb('community', props.contextType, props.contextData)}
              username={routerProps.match.params.username}
            />
          )
        }
      }
    ]}
  />

CommunityTool.propTypes = {
  contextType: T.string,
  contextData: T.object,
  path: T.string.isRequired,
  workspace: T.object,
  canAdministrate: T.bool.isRequired
}

export {
  CommunityTool
}
