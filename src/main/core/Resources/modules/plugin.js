/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Core plugin.
 */
registry.add('ClarolineCoreBundle', {
  /**
   * Provides new tabs for the administration integration tool.
   */
  integration: {
    'api': () => { return import(/* webpackChunkName: "core-integration-api" */ '#/main/core/integration/api')}
  },

  /**
   * Provides searchable items for the global search.
   */
  search: {
    'resource' : () => { return import(/* webpackChunkName: "core-search-resource" */  '#/main/core/search/resource')},
    'workspace': () => { return import(/* webpackChunkName: "core-search-workspace" */ '#/main/core/search/workspace')}
  },

  /**
   * Provides pages for the user editor.
   */
  account: {
    // 'favourites': () => { return import(/* webpackChunkName: "core-account-favourites" */ '#/main/core/account/favourites')},
    'history': () => { return import(/* webpackChunkName: "core-account-history" */ '#/main/core/account/history')}
  },

  /**
   * Provides tabs for the user profile.
   */
  profile: {
    'workspaces': () => { return import(/* webpackChunkName: "core-profile-workspaces" */ '#/main/core/profile/workspaces')}
  },

  /**
   * Provides actions for base Claroline objects.
   */
  actions: {
    tool: {
      'open': () => { return import(/* webpackChunkName: "core-action-tool-open" */ '#/main/core/actions/tool/open') },
      'configure': () => { return import(/* webpackChunkName: "core-action-tool-configure" */ '#/main/core/actions/tool/configure') }
    },

    resource: {
      // all resources
      'open-parent'   : () => { return import(/* webpackChunkName: "core-action-resource-open-parent" */ '#/main/core/actions/resource/open-parent') },
      'configure'     : () => { return import(/* webpackChunkName: "core-action-resource-configure" */ '#/main/core/actions/resource/configure') },
      'copy'          : () => { return import(/* webpackChunkName: "core-action-resource-copy" */      '#/main/core/actions/resource/copy') },
      'delete'        : () => { return import(/* webpackChunkName: "core-action-resource-delete" */    '#/main/core/actions/resource/delete') },
      'download'      : () => { return import(/* webpackChunkName: "core-action-resource-export" */    '#/main/core/actions/resource/download') },
      'move'          : () => { return import(/* webpackChunkName: "core-action-resource-move" */      '#/main/core/actions/resource/move') },
      'open'          : () => { return import(/* webpackChunkName: "core-action-resource-open" */      '#/main/core/actions/resource/open') },
      'publish'       : () => { return import(/* webpackChunkName: "core-action-resource-publish" */   '#/main/core/actions/resource/publish') },
      'restore'       : () => { return import(/* webpackChunkName: "core-action-resource-restore" */   '#/main/core/actions/resource/restore') },
      'archive'       : () => { return import(/* webpackChunkName: "core-action-resource-archive" */   '#/main/core/actions/resource/archive') },
      'unpublish'     : () => { return import(/* webpackChunkName: "core-action-resource-unpublish" */ '#/main/core/actions/resource/unpublish') },
      'show-dashboard': () => { return import(/* webpackChunkName: "core-action-resource-dashboard" */ '#/main/core/actions/resource/show-dashboard') }
    },

    directory: {
      'add': () => { return import(/* webpackChunkName: "core-action-resource-add" */ '#/main/core/resources/directory/actions/add') },
      'apply-rights': () => { return import(/* webpackChunkName: "core-action-resource-apply-rights" */ '#/main/core/resources/directory/actions/apply-rights') }
    },

    workspace: {
      'archive'  : () => { return import(/* webpackChunkName: "core-action-workspace-archive" */   '#/main/core/actions/workspace/archive') },
      'copy'     : () => { return import(/* webpackChunkName: "core-action-workspace-copy" */      '#/main/core/actions/workspace/copy') },
      'delete'   : () => { return import(/* webpackChunkName: "core-action-workspace-delete" */    '#/main/core/actions/workspace/delete') },
      'export'   : () => { return import(/* webpackChunkName: "core-action-workspace-export" */    '#/main/core/actions/workspace/export') },
      'restore': () => { return import(/* webpackChunkName: "core-action-workspace-restore" */ '#/main/core/actions/workspace/restore') }
    },

    user: {
      'ws-register': () => { return import(/* webpackChunkName: "core-action-user-ws-register" */ '#/main/core/actions/user/ws-register') }
    },

    group: {
      'ws-register': () => { return import(/* webpackChunkName: "core-action-group-ws-register" */ '#/main/core/actions/group/ws-register') }
    },

    context: {
      'open'     : () => { return import(/* webpackChunkName: "core-action-context-open" */      '#/main/core/actions/context/open') },
      'configure': () => { return import(/* webpackChunkName: "core-action-context-configure" */ '#/main/core/actions/context/configure') }
    }
  },

  /**
   * Provides new types of resources.
   */
  resources: {
    'directory': () => { return import(/* webpackChunkName: "core-resource-directory" */ '#/main/core/resources/directory') },
    'file'     : () => { return import(/* webpackChunkName: "core-resource-file" */ '#/main/core/resources/file') }
  },

  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'workspaces': () => { return import(/* webpackChunkName: "core-tool-workspaces" */ '#/main/core/tools/workspaces') },
    'resources' : () => { return import(/* webpackChunkName: "core-tool-resources" */  '#/main/core/tools/resources') },
    'locations' : () => { return import(/* webpackChunkName: "core-tool-locations" */  '#/main/core/tools/locations') }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'parameters'         : () => { return import(/* webpackChunkName: "core-admin-parameters" */  '#/main/core/administration/parameters') },
    'integration'        : () => { return import(/* webpackChunkName: "core-admin-integration" */ '#/main/core/administration/integration') },
    'connection_messages': () => { return import(/* webpackChunkName: "core-admin-connection-messages" */ '#/main/core/administration/connection-messages') },
  },

  /**
   * Provides new Widgets for homes.
   */
  widgets: {
    'list'    : () => { return import(/* webpackChunkName: "core-widget-list" */     '#/main/core/widget/types/list') },
    'simple'  : () => { return import(/* webpackChunkName: "core-widget-simple" */   '#/main/core/widget/types/simple') },
    'resource': () => { return import(/* webpackChunkName: "core-widget-resource" */ '#/main/core/widget/types/resource') }
  },

  data: {
    types: {
      'location' : () => { return import(/* webpackChunkName: "core-data-type-location" */  '#/main/core/data/types/location') },
      'resource' : () => { return import(/* webpackChunkName: "core-data-type-resource" */  '#/main/core/data/types/resource') },
      'workspace': () => { return import(/* webpackChunkName: "core-data-type-workspace" */ '#/main/core/data/types/workspace') }
    },
    sources: {
      'resources'    : () => { return import(/* webpackChunkName: "core-data-source-resources" */    '#/main/core/data/sources/resources') },
      'workspaces'   : () => { return import(/* webpackChunkName: "core-data-source-workspaces" */   '#/main/core/data/sources/workspaces') },
      'my_workspaces': () => { return import(/* webpackChunkName: "core-data-source-m-workspaces" */ '#/main/core/data/sources/workspaces') },
      'tools'        : () => { return import(/* webpackChunkName: "core-data-source-tools" */        '#/main/core/data/sources/tools') }
    }
  }
})
