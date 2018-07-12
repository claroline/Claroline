import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'
import {currentUser} from '#/main/core/user/current'
import {isAdmin} from  '#/main/core/workspace/user/restrictions'

// app sections
import {ParametersTab} from '#/main/core/administration/user/parameters/components/parameters-tab'
import {UserTab, UserTabActions} from '#/main/core/administration/user/user/components/user-tab'
import {GroupTab, GroupTabActions} from '#/main/core/administration/user/group/components/group-tab'
import {RoleTab, RoleTabActions} from '#/main/core/administration/user/role/components/role-tab'
import {OrganizationTab, OrganizationTabActions} from '#/main/core/administration/user/organization/components/organization-tab'
import {ProfileTab} from '#/main/core/administration/user/profile/components/profile-tab'
import {LocationTab, LocationTabActions} from '#/main/core/administration/user/location/components/location-tab'

const UserTool = () =>
  <TabbedPageContainer
    title={trans('users_management', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/users'}
    ]}

    tabs={[
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
        displayed: isAdmin(currentUser()),
        actions: RoleTabActions,
        content: RoleTab
      }, {
        icon: 'fa fa-id-card-o',
        title: trans('user_profile'),
        path: '/profile',
        displayed: isAdmin(currentUser()),
        content: ProfileTab
      }, {
        icon: 'fa fa-cog',
        title: trans('parameters'),
        path: '/parameters',
        onlyIcon: true,
        displayed: isAdmin(currentUser()),
        content: ParametersTab
      }
    ]}
  />

export {
  UserTool
}
