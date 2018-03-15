import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'
import {UserTab, UserTabActions} from '#/main/core/workspace/user/user/components/user-tab.jsx'
import {GroupTab, GroupTabActions} from '#/main/core/workspace/user/group/components/group-tab.jsx'
import {RoleTab, RoleTabActions} from '#/main/core/workspace/user/role/components/role-tab.jsx'
import {ParametersTab, ParametersTabActions} from '#/main/core/workspace/user/parameters/components/parameters-tab.jsx'
import {PendingTab} from '#/main/core/workspace/user/pending/components/pending-tab.jsx'
import {connect} from 'react-redux'
import {currentUser} from '#/main/core/user/current'
import {select}  from '#/main/core/workspace/user/selectors'
import {READ_ONLY, MANAGER, ADMIN, getPermissionLevel} from  '#/main/core/workspace/user/restrictions'

const Tool = (props) => {
  const permLevel = getPermissionLevel(currentUser(), props.workspace)
  const tabs = [
    {
      icon: 'fa fa-user',
      title: trans('users'),
      path: '/users',
      content: UserTab,
      //perm check here for creation
      actions: permLevel === MANAGER || permLevel === ADMIN ? UserTabActions: null
    },
    {
      icon: 'fa fa-users',
      title: trans('groups'),
      path: '/groups',
      content: GroupTab,
      //perm check here for creation
      actions: permLevel === MANAGER || permLevel === ADMIN ? GroupTabActions: null
    },
    {
      icon: 'fa fa-id-badge',
      title: trans('roles'),
      path: '/roles',
      content: RoleTab,
      actions: RoleTabActions
    },
    {
      icon: 'fa fa-book',
      title: trans('pending'),
      path: '/pendings',
      content: PendingTab
    },
    {
      icon: 'fa fa-cog',
      title: trans('parameters'),
      onlyIcon: true,
      path: '/parameters',
      content: ParametersTab,
      actions: ParametersTabActions
    }
  ]

  if (permLevel === READ_ONLY) {
    const irole = tabs.findIndex(tab => tab.path === '/roles')
    tabs.splice(irole, 1)
    const ipending = tabs.findIndex(tab => tab.path === '/pendings')
    tabs.splice(ipending, 1)
    const iparam = tabs.findIndex(tab => tab.path === '/parameters')
    tabs.splice(iparam, 1)
  }

  if (props.workspace.meta.model) {
    const igroup = tabs.findIndex(tab => tab.path === '/groups')
    tabs.splice(igroup, 1)
    const iuser = tabs.findIndex(tab => tab.path === '/users')
    tabs.splice(iuser, 1)
  }

  return (
    <TabbedPageContainer
      title={trans('workspace_management', {}, 'tools')}
      redirect={[
        {from: '/', exact: true, to: '/users'}
      ]}
      tabs={tabs}
    />
  )
}

const UserTool = connect(
  state => ({
    workspace: select.workspace(state)
  })
)(Tool)

export {
  UserTool
}
