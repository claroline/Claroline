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
      'impersonation': () => { return import(/* webpackChunkName: "community-action-desktop-impersonation" */ '#/main/community/desktop/actions/impersonation') },
    },

    workspace: {
      'register-users' : () => { return import(/* webpackChunkName: "community-action-workspace-register-users" */  '#/main/community/workspace/actions/register-users') },
      'register-groups': () => { return import(/* webpackChunkName: "community-action-workspace-register-groups" */ '#/main/community/workspace/actions/register-groups') },
      'register-self'  : () => { return import(/* webpackChunkName: "community-action-workspace-register-self" */   '#/main/community/workspace/actions/register-self') },
      'unregister-self': () => { return import(/* webpackChunkName: "community-action-workspace-unregister-self" */ '#/main/community/workspace/actions/unregister-self') },
      'view-as'        : () => { return import(/* webpackChunkName: "community-action-workspace-view-as" */         '#/main/community/workspace/actions/view-as') }
    },

    user: {
      'disable'        : () => { return import(/* webpackChunkName: "community-action-user-disable" */         '#/main/community/user/actions/disable') },
      'enable'         : () => { return import(/* webpackChunkName: "community-action-user-enable" */          '#/main/community/user/actions/enable') },
      'password-change': () => { return import(/* webpackChunkName: "community-action-user-password-change" */ '#/main/community/user/actions/password-change') },
      'password-reset' : () => { return import(/* webpackChunkName: "community-action-user-password-reset" */  '#/main/community/user/actions/password-reset') },
      'show-as'        : () => { return import(/* webpackChunkName: "community-action-user-show-as" */         '#/main/community/user/actions/show-as') },
      'show-profile'   : () => { return import(/* webpackChunkName: "community-action-user-show-profile" */    '#/main/community/user/actions/show-profile') }
    }
  },

  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'community': () => { return import(/* webpackChunkName: "core-tool-users" */ '#/main/community/tools/community') }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'community': () => { return import(/* webpackChunkName: "core-admin-users" */ '#/main/community/administration/community') }
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
