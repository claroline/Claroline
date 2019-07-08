import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

// app sections
import {ParametersTab} from '#/main/core/administration/users/parameters/components/parameters-tab'
import {UserTab, UserTabActions} from '#/main/core/administration/users/user/components/user-tab'
import {GroupTab, GroupTabActions} from '#/main/core/administration/users/group/components/group-tab'
import {RoleTab, RoleTabActions} from '#/main/core/administration/users/role/components/role-tab'
import {OrganizationTab, OrganizationTabActions} from '#/main/core/administration/users/organization/components/organization-tab'
import {ProfileTab} from '#/main/core/administration/users/profile/components/profile-tab'
import {LocationTab, LocationTabActions} from '#/main/core/administration/users/location/components/location-tab'

const UsersTool = (props) =>
  <TabbedPageContainer
    title={trans('users_management', {}, 'tools')}

    path={props.path}
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
        displayed: props.isAdmin,
        actions: RoleTabActions,
        content: RoleTab
      }, {
        icon: 'fa fa-id-card-o',
        title: trans('user_profile'),
        path: '/profile',
        displayed: props.isAdmin,
        content: ProfileTab
      }, {
        icon: 'fa fa-cog',
        title: trans('parameters'),
        path: '/parameters',
        onlyIcon: true,
        displayed: props.isAdmin,
        content: ParametersTab
      }
    ]}
  />

UsersTool.propTypes = {
  path: T.string.isRequired,
  isAdmin: T.bool.isRequired
}

export {
  UsersTool
}
