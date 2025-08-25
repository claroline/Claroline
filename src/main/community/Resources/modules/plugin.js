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
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'community': () => { return import(/* webpackChunkName: "community-tool-community" */ '#/main/community/tools/community') }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'organizations': () => { return import(/* webpackChunkName: "community-tool-organizations" */ '#/main/community/tools/organizations') }
  },

  /**
   * Provides actions for base Claroline objects.
   */
  actions: {
    community: {
      'disable-inactive': () => { return import(/* webpackChunkName: "community-action-disable-inactive" */ '#/main/community/tools/community/actions/disable-inactive') },
      'generate-user-roles': () => { return import(/* webpackChunkName: "community-action-generate-user-roles" */ '#/main/community/tools/community/actions/generate-user-roles') }
    },
    user: {
      'open'      : () => { return import(/* webpackChunkName: "community-action-user-open" */    '#/main/community/actions/user/open') },
      'edit'      : () => { return import(/* webpackChunkName: "community-action-user-edit" */    '#/main/community/actions/user/edit') },
      'disable'   : () => { return import(/* webpackChunkName: "community-action-user-disable" */ '#/main/community/actions/user/disable') },
      'enable'    : () => { return import(/* webpackChunkName: "community-action-user-enable" */  '#/main/community/actions/user/enable') },
      'view-as'   : () => { return import(/* webpackChunkName: "community-action-user-view-as" */ '#/main/community/actions/user/view-as') },
      'delete'    : () => { return import(/* webpackChunkName: "community-action-user-delete" */  '#/main/community/actions/user/delete') },
      'add-groups': () => { return import(/* webpackChunkName: "core-action-user-add-groups" */   '#/main/community/actions/user/add-groups') },
      'export': () => { return import(/* webpackChunkName: "core-action-user-export" */   '#/main/community/actions/user/export') },
      'request-deletion': () => { return import(/* webpackChunkName: "core-action-user-request-deletion" */   '#/main/community/actions/user/request-deletion') }
    },

    group: {
      'open'     : () => { return import(/* webpackChunkName: "community-action-group-open" */   '#/main/community/actions/group/open') },
      'edit'     : () => { return import(/* webpackChunkName: "community-action-group-edit" */   '#/main/community/actions/group/edit') },
      'delete'   : () => { return import(/* webpackChunkName: "community-action-group-delete" */ '#/main/community/actions/group/delete') },
      'add-users': () => { return import(/* webpackChunkName: "community-action-group-add-users" */   '#/main/community/actions/group/add-users') },
    },

    role: {
      'open'   : () => { return import(/* webpackChunkName: "community-action-role-open" */    '#/main/community/actions/role/open') },
      'edit'   : () => { return import(/* webpackChunkName: "community-action-role-edit" */    '#/main/community/actions/role/edit') },
      'delete' : () => { return import(/* webpackChunkName: "community-action-role-delete" */  '#/main/community/actions/role/delete') },
      'view-as': () => { return import(/* webpackChunkName: "community-action-role-view-as" */ '#/main/community/actions/role/view-as') },
      'add-users': () => { return import(/* webpackChunkName: "community-action-role-add-users" */ '#/main/community/actions/role/add-users') },
      'add-groups': () => { return import(/* webpackChunkName: "community-action-role-add-groups" */ '#/main/community/actions/role/add-groups') }
    },

    organization: {
      'open'  : () => { return import(/* webpackChunkName: "community-action-organization-open" */   '#/main/community/actions/organization/open') },
      'browse'  : () => { return import(/* webpackChunkName: "community-action-organization-browse" */   '#/main/community/actions/organization/browse') },
      'edit'  : () => { return import(/* webpackChunkName: "community-action-organization-edit" */   '#/main/community/actions/organization/edit') },
      'delete': () => { return import(/* webpackChunkName: "community-action-organization-delete" */ '#/main/community/actions/organization/delete') }
    },

    team: {
      'open'  : () => { return import(/* webpackChunkName: "community-action-team-open" */   '#/main/community/actions/team/open') },
      'edit'  : () => { return import(/* webpackChunkName: "community-action-team-edit" */   '#/main/community/actions/team/edit') },
      'fill': () => { return import(/* webpackChunkName: "community-action-team-fill" */ '#/main/community/actions/team/fill') },
      'empty': () => { return import(/* webpackChunkName: "community-action-team-empty" */ '#/main/community/actions/team/empty') },
      'delete': () => { return import(/* webpackChunkName: "community-action-team-delete" */ '#/main/community/actions/team/delete') }
    },
  },

  data: {
    types: {
      'group'        : () => { return import(/* webpackChunkName: "community-data-type-group" */        '#/main/community/data/types/group') },
      'organization' : () => { return import(/* webpackChunkName: "community-data-type-organization" */ '#/main/community/data/types/organization') },
      'role'         : () => { return import(/* webpackChunkName: "community-data-type-role" */         '#/main/community/data/types/role') },
      'user'         : () => { return import(/* webpackChunkName: "community-data-type-user" */         '#/main/community/data/types/user') },
      'team'         : () => { return import(/* webpackChunkName: "community-data-type-team" */         '#/main/community/data/types/team') }
    },
    sources: {
      'users': () => { return import(/* webpackChunkName: "community-data-source-users" */ '#/main/community/data/sources/users') },
      'teams': () => { return import(/* webpackChunkName: "community-data-source-teams" */ '#/main/community/data/sources/teams') },
      'my-teams': () => { return import(/* webpackChunkName: "community-data-source-my-teams" */ '#/main/community/data/sources/my-teams') },
      'groups': () => { return import(/* webpackChunkName: "community-data-source-groups" */ '#/main/community/data/sources/groups') },
      'my-groups': () => { return import(/* webpackChunkName: "community-data-source-my-groups" */ '#/main/community/data/sources/my-groups') },
      'roles': () => { return import(/* webpackChunkName: "community-data-source-roles" */ '#/main/community/data/sources/roles') },
      'my-roles': () => { return import(/* webpackChunkName: "community-data-source-my-roles" */ '#/main/community/data/sources/my-roles') },
      'teams-members': () => { return import(/* webpackChunkName: "community-data-source-teams-members" */ '#/main/community/data/sources/teams-members') },
      'organizations': () => { return import(/* webpackChunkName: "community-data-source-organizations" */ '#/main/community/data/sources/organizations') },
      'my-organizations': () => { return import(/* webpackChunkName: "community-data-source-my-organizations" */ '#/main/community/data/sources/my-organizations') }
    }
  },

  badge_rules: {
    'in_group': () => { return import(/* webpackChunkName: "community-badge-in_group" */ '#/main/community/badge_rules/in_group') },
    'in_role': () => { return import(/* webpackChunkName: "community-badge-in_role" */ '#/main/community/badge_rules/in_role') },
    'in_team': () => { return import(/* webpackChunkName: "community-badge-in_team" */ '#/main/community/badge_rules/in_team') }
  }
})
