/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Community plugin.
 */
registry.add('ClarolineCommunityBundle', {
  /**
   * Provides searchable items for the global search.
   */
  search: {
    'user': () => { return import(/* webpackChunkName: "community-search-user" */ '#/main/community/search/user')}
  },

  /**
   * Provides actions for base Claroline objects.
   */
  actions: {
    desktop: {
      'view-as': () => { return import(/* webpackChunkName: "community-action-desktop-impersonation" */ '#/main/community/actions/desktop/view-as') },
    },

    workspace: {
      'register-users' : () => { return import(/* webpackChunkName: "community-action-workspace-register-users" */  '#/main/community/actions/workspace/register-users') },
      'register-groups': () => { return import(/* webpackChunkName: "community-action-workspace-register-groups" */ '#/main/community/actions/workspace/register-groups') },
      'register-self'  : () => { return import(/* webpackChunkName: "community-action-workspace-register-self" */   '#/main/community/actions/workspace/register-self') },
      'unregister-self': () => { return import(/* webpackChunkName: "community-action-workspace-unregister-self" */ '#/main/community/actions/workspace/unregister-self') },
      'view-as'        : () => { return import(/* webpackChunkName: "community-action-workspace-view-as" */         '#/main/community/actions/workspace/view-as') }
    },

    user: {
      'about'          : () => { return import(/* webpackChunkName: "community-action-user-about" */           '#/main/community/actions/user/about') },
      'open'           : () => { return import(/* webpackChunkName: "community-action-user-open" */            '#/main/community/actions/user/open') },
      'edit'           : () => { return import(/* webpackChunkName: "community-action-user-edit" */            '#/main/community/actions/user/edit') },
      'disable'        : () => { return import(/* webpackChunkName: "community-action-user-disable" */         '#/main/community/actions/user/disable') },
      'enable'         : () => { return import(/* webpackChunkName: "community-action-user-enable" */          '#/main/community/actions/user/enable') },
      'password-change': () => { return import(/* webpackChunkName: "community-action-user-password-change" */ '#/main/community/actions/user/password-change') },
      'password-reset' : () => { return import(/* webpackChunkName: "community-action-user-password-reset" */  '#/main/community/actions/user/password-reset') },
      'view-as'        : () => { return import(/* webpackChunkName: "community-action-user-view-as" */         '#/main/community/actions/user/view-as') },
      'delete'         : () => { return import(/* webpackChunkName: "community-action-user-delete" */          '#/main/community/actions/user/delete') }
    },

    group: {
      'about'         : () => { return import(/* webpackChunkName: "community-action-group-about" */          '#/main/community/actions/group/about') },
      'open'          : () => { return import(/* webpackChunkName: "community-action-group-open" */           '#/main/community/actions/group/open') },
      'edit'          : () => { return import(/* webpackChunkName: "community-action-group-edit" */           '#/main/community/actions/group/edit') },
      'password-reset': () => { return import(/* webpackChunkName: "community-action-group-password-reset" */ '#/main/community/actions/group/password-reset') },
      'delete'        : () => { return import(/* webpackChunkName: "community-action-group-delete" */         '#/main/community/actions/group/delete') }
    },

    role: {
      'about'  : () => { return import(/* webpackChunkName: "community-action-role-about" */   '#/main/community/actions/role/about') },
      'open'   : () => { return import(/* webpackChunkName: "community-action-role-open" */    '#/main/community/actions/role/open') },
      'edit'   : () => { return import(/* webpackChunkName: "community-action-role-edit" */    '#/main/community/actions/role/edit') },
      'delete' : () => { return import(/* webpackChunkName: "community-action-role-delete" */  '#/main/community/actions/role/delete') },
      'view-as': () => { return import(/* webpackChunkName: "community-action-role-view-as" */ '#/main/community/actions/role/view-as') }
    },

    organization: {
      'about' : () => { return import(/* webpackChunkName: "community-action-organization-about" */  '#/main/community/actions/organization/about') },
      'open'  : () => { return import(/* webpackChunkName: "community-action-organization-open" */   '#/main/community/actions/organization/open') },
      'edit'  : () => { return import(/* webpackChunkName: "community-action-organization-edit" */   '#/main/community/actions/organization/edit') },
      'delete': () => { return import(/* webpackChunkName: "community-action-organization-delete" */ '#/main/community/actions/organization/delete') }
    },

    team: {
      'about' : () => { return import(/* webpackChunkName: "community-action-team-about" */  '#/main/community/actions/team/about') },
      'open'  : () => { return import(/* webpackChunkName: "community-action-team-open" */   '#/main/community/actions/team/open') },
      'edit'  : () => { return import(/* webpackChunkName: "community-action-team-edit" */   '#/main/community/actions/team/edit') },
      'fill': () => { return import(/* webpackChunkName: "community-action-team-fill" */ '#/main/community/actions/team/fill') },
      'empty': () => { return import(/* webpackChunkName: "community-action-team-empty" */ '#/main/community/actions/team/empty') },
      'delete': () => { return import(/* webpackChunkName: "community-action-team-delete" */ '#/main/community/actions/team/delete') },
    },
  },

  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'community': () => { return import(/* webpackChunkName: "core-tool-users" */ '#/main/community/tools/community') }
  },

  /**
   * Provides current user Account sections.
   */
  account: {
    'profile'   : () => { return import(/* webpackChunkName: "core-account-profile" */ '#/main/community/account/profile') }
  },

  data: {
    types: {
      'group'        : () => { return import(/* webpackChunkName: "core-data-type-group" */         '#/main/community/data/types/group') },
      'groups'       : () => { return import(/* webpackChunkName: "core-data-type-groups" */        '#/main/community/data/types/groups') },
      'organization' : () => { return import(/* webpackChunkName: "core-data-type-organization" */  '#/main/community/data/types/organization') },
      'organizations': () => { return import(/* webpackChunkName: "core-data-type-organizations" */ '#/main/community/data/types/organizations') },
      'role'         : () => { return import(/* webpackChunkName: "core-data-type-role" */          '#/main/community/data/types/role') },
      'roles'        : () => { return import(/* webpackChunkName: "core-data-type-roles" */         '#/main/community/data/types/roles') },
      'user'         : () => { return import(/* webpackChunkName: "core-data-type-user" */          '#/main/community/data/types/user') },
      'users'        : () => { return import(/* webpackChunkName: "core-data-type-users" */         '#/main/community/data/types/users') },
    },
    sources: {
      'users': () => { return import(/* webpackChunkName: "core-data-source-users" */ '#/main/community/data/sources/users') }
    }
  }
})
