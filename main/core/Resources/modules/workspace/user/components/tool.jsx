import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {currentUser} from '#/main/core/user/current'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {UserTab, UserTabActions} from '#/main/core/workspace/user/user/components/user-tab.jsx'
import {GroupTab, GroupTabActions} from '#/main/core/workspace/user/group/components/group-tab.jsx'
import {RoleTab, RoleTabActions} from '#/main/core/workspace/user/role/components/role-tab.jsx'
import {ParametersTab, ParametersTabActions} from '#/main/core/workspace/user/parameters/components/parameters-tab.jsx'
import {PendingTab} from '#/main/core/workspace/user/pending/components/pending-tab.jsx'

import {select}  from '#/main/core/workspace/user/selectors'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {READ_ONLY, MANAGER, ADMIN, getPermissionLevel} from  '#/main/core/workspace/user/restrictions'

const Tool = (props) => {
  const permLevel = getPermissionLevel(currentUser(), props.workspace)

  return (
    <TabbedPageContainer
      title={trans('users')}
      redirect={[
        {from: '/', exact: true, to: '/users'}
      ]}
      tabs={[
        {
          icon: 'fa fa-user',
          title: trans('users'),
          path: '/users',
          content: UserTab,
          //perm check here for creation
          actions: permLevel === MANAGER || permLevel === ADMIN ? UserTabActions: null,
          displayed: !props.workspace.meta.model
        }, {
          icon: 'fa fa-users',
          title: trans('groups'),
          path: '/groups',
          content: GroupTab,
          //perm check here for creation
          actions: permLevel === MANAGER || permLevel === ADMIN ? GroupTabActions: null,
          displayed: !props.workspace.meta.model
        }, {
          icon: 'fa fa-id-badge',
          title: trans('roles'),
          path: '/roles',
          content: RoleTab,
          actions: RoleTabActions,
          displayed: permLevel !== READ_ONLY
        }, {
          icon: 'fa fa-user-plus',
          title: trans('pending_registrations'),
          path: '/pending',
          content: PendingTab,
          displayed: permLevel !== READ_ONLY && props.workspace.registration.selfRegistration && props.workspace.registration.validation
        }, {
          icon: 'fa fa-cog',
          title: trans('parameters'),
          onlyIcon: true,
          path: '/parameters',
          content: ParametersTab,
          actions: ParametersTabActions,
          displayed: permLevel !== READ_ONLY
        }
      ]}
    />
  )
}

Tool.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired
}

const UserTool = connect(
  state => ({
    workspace: select.workspace(state)
  })
)(Tool)

export {
  UserTool
}
