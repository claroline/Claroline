import React from 'react'

import {DragDropContext} from 'react-dnd'
import {default as TouchBackend} from 'react-dnd-touch-backend'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

// app sections
import {ParametersTab, ParametersTabActions} from '#/main/core/administration/user/parameters/components/parameters-tab.jsx'
import {UserTab, UserTabActions} from '#/main/core/administration/user/user/components/user-tab.jsx'
import {GroupTab, GroupTabActions} from '#/main/core/administration/user/group/components/group-tab.jsx'
import {RoleTab, RoleTabActions} from '#/main/core/administration/user/role/components/role-tab.jsx'
import {OrganizationTab, OrganizationTabActions} from '#/main/core/administration/user/organization/components/organization-tab.jsx'
import {ProfileTab, ProfileTabActions} from '#/main/core/administration/user/profile/components/profile-tab.jsx'
import {LocationTab, LocationTabActions} from '#/main/core/administration/user/location/components/location-tab.jsx'
import {currentUser} from '#/main/core/user/current'
import {isAdmin} from  '#/main/core/workspace/user/restrictions'

const Tool = () => {
  const tabs = [
    {
      icon: 'fa fa-user',
      title: trans('users'),
      path: '/users',
      actions: UserTabActions,
      content: UserTab
    }, {
      icon: 'fa fa-users',
      title: trans('groups'),
      path: '/groups',
      actions: GroupTabActions,
      content: GroupTab
    }, {
      icon: 'fa fa-building',
      title: trans('organizations'),
      path: '/organizations',
      actions: OrganizationTabActions,
      content: OrganizationTab
    }, {
      icon: 'fa fa-location-arrow',
      title: trans('locations'),
      path: '/locations',
      actions: LocationTabActions,
      content: LocationTab
    }, {
      icon: 'fa fa-id-badge',
      title: trans('roles'),
      path: '/roles',
      actions: RoleTabActions,
      content: RoleTab
    }, {
      icon: 'fa fa-id-card-o',
      title: trans('user_profile'),
      path: '/profile',
      actions: ProfileTabActions,
      content: ProfileTab
    }, {
      icon: 'fa fa-cog',
      title: trans('parameters'),
      path: '/parameters',
      onlyIcon: true,
      actions: ParametersTabActions,
      content: ParametersTab
    }
  ]

  if (!isAdmin(currentUser())) {
    const irole = tabs.findIndex(tab => tab.path === '/roles')
    tabs.splice(irole, 1)
    const iprofile = tabs.findIndex(tab => tab.path === '/profile')
    tabs.splice(iprofile, 1)
    const iparameters = tabs.findIndex(tab => tab.path === '/parameters')
    tabs.splice(iparameters, 1)
  }

  return (<TabbedPageContainer
    title={trans('users_management', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/users'}
    ]}

    tabs={tabs}
  />)
}

const ToolDnD = DragDropContext(TouchBackend({ enableMouseEvents: true }))(Tool)

export {
  ToolDnD as UserTool
}
