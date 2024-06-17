/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Core plugin.
 */
registry.add('ClarolineCoreBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  header: {
    'search': () => { return import(/* webpackChunkName: "core-header-search" */ '#/main/core/header/search') }
  },

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
   * Provides actions for base Claroline objects.
   */
  actions: {
    account: {},
    administration: {},
    desktop: {},

    tool: {
      'configure': () => { return import(/* webpackChunkName: "core-action-tool-configure" */ '#/main/core/actions/tool/configure') },
      'rights'   : () => { return import(/* webpackChunkName: "core-action-tool-rights" */    '#/main/core/actions/tool/rights') }
    },

    resource: {
      // all resources
      'about'    : () => { return import(/* webpackChunkName: "core-action-resource-about" */       '#/main/core/actions/resource/about') },
      'configure': () => { return import(/* webpackChunkName: "core-action-resource-configure" */   '#/main/core/actions/resource/configure') },
      'copy'     : () => { return import(/* webpackChunkName: "core-action-resource-copy" */        '#/main/core/actions/resource/copy') },
      'delete'   : () => { return import(/* webpackChunkName: "core-action-resource-delete" */      '#/main/core/actions/resource/delete') },
      'edit'     : () => { return import(/* webpackChunkName: "core-action-resource-edit" */        '#/main/core/actions/resource/edit') },
      'export'   : () => { return import(/* webpackChunkName: "core-action-resource-export" */      '#/main/core/actions/resource/export') },
      'move'     : () => { return import(/* webpackChunkName: "core-action-resource-move" */        '#/main/core/actions/resource/move') },
      'open'     : () => { return import(/* webpackChunkName: "core-action-resource-open" */        '#/main/core/actions/resource/open') },
      'publish'  : () => { return import(/* webpackChunkName: "core-action-resource-publish" */     '#/main/core/actions/resource/publish') },
      'restore'  : () => { return import(/* webpackChunkName: "core-action-resource-restore" */     '#/main/core/actions/resource/restore') },
      'rights'   : () => { return import(/* webpackChunkName: "core-action-resource-rights" */      '#/main/core/actions/resource/rights') },
      'unpublish': () => { return import(/* webpackChunkName: "core-action-resource-unpublish" */   '#/main/core/actions/resource/unpublish') },

      // directory resource
      'add'       : () => { return import(/* webpackChunkName: "core-action-resource-add" */       '#/main/core/resources/directory/actions/add') },
      'add_files' : () => { return import(/* webpackChunkName: "core-action-resource-add-files" */ '#/main/core/resources/directory/actions/add-files') },

      // file resource
      'download'   : () => { return import(/* webpackChunkName: "core-action-resource-download" */    '#/main/core/resources/file/actions/download') },
      'change_file': () => { return import(/* webpackChunkName: "core-action-resource-change-file" */ '#/main/core/resources/file/actions/change-file') }
    },

    workspace: {
      'about'    : () => { return import(/* webpackChunkName: "core-action-workspace-about" */     '#/main/core/actions/workspace/about') },
      'archive'  : () => { return import(/* webpackChunkName: "core-action-workspace-archive" */   '#/main/core/actions/workspace/archive') },
      'configure': () => { return import(/* webpackChunkName: "core-action-workspace-configure" */ '#/main/core/actions/workspace/configure') },
      'copy'     : () => { return import(/* webpackChunkName: "core-action-workspace-copy" */      '#/main/core/actions/workspace/copy') },
      'delete'   : () => { return import(/* webpackChunkName: "core-action-workspace-delete" */    '#/main/core/actions/workspace/delete') },
      'export'   : () => { return import(/* webpackChunkName: "core-action-workspace-export" */    '#/main/core/actions/workspace/export') },
      'open'     : () => { return import(/* webpackChunkName: "core-action-workspace-open" */      '#/main/core/actions/workspace/open') },
      'unarchive': () => { return import(/* webpackChunkName: "core-action-workspace-unarchive" */ '#/main/core/actions/workspace/unarchive') }
    },

    user: {
      'ws-disable': () => { return import(/* webpackChunkName: "core-action-user-ws-disable" */ '#/main/core/actions/user/ws-disable') },
      'ws-enable' : () => { return import(/* webpackChunkName: "core-action-user-ws-enable" */  '#/main/core/actions/user/ws-enable') },
      'ws-register': () => { return import(/* webpackChunkName: "core-action-user-ws-register" */ '#/main/core/actions/user/ws-register') }
    },

    group: {
      'ws-register': () => { return import(/* webpackChunkName: "core-action-group-ws-register" */ '#/main/core/actions/group/ws-register') }
    },

    role: {
      'add-users': () => { return import(/* webpackChunkName: "core-action-role-add-users" */ '#/main/core/actions/role/add-users') },
      'add-groups': () => { return import(/* webpackChunkName: "core-action-role-add-groups" */ '#/main/core/actions/role/add-groups') }
    }
  },

  /**
   * Provides new types of resources.
   */
  resources: {
    'directory': () => { return import(/* webpackChunkName: "core-resource-directory" */ '#/main/core/resources/directory') },
    'file'     : () => { return import(/* webpackChunkName: "core-resource-file" */      '#/main/core/resources/file') },
    'text'     : () => { return import(/* webpackChunkName: "core-resource-text" */      '#/main/core/resources/text') }
  },

  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'workspaces'     : () => { return import(/* webpackChunkName: "core-tool-workspaces" */ '#/main/core/tools/workspaces') },
    'resources'      : () => { return import(/* webpackChunkName: "core-tool-resources" */  '#/main/core/tools/resources') },
    'locations'      : () => { return import(/* webpackChunkName: "core-tool-locations" */  '#/main/core/tools/locations') }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'parameters'         : () => { return import(/* webpackChunkName: "core-admin-parameters" */  '#/main/core/administration/parameters') },
    'templates'          : () => { return import(/* webpackChunkName: "core-admin-template" */    '#/main/core/administration/template') },
    'integration'        : () => { return import(/* webpackChunkName: "core-admin-integration" */ '#/main/core/administration/integration') },
    'connection_messages': () => { return import(/* webpackChunkName: "core-admin-connection-messages" */ '#/main/core/administration/connection-messages') },
  },

  /**
   * Provides current user Account sections.
   */
  account: {
    'parameters': () => { return import(/* webpackChunkName: "core-account-parameters" */ '#/main/core/account/parameters') },
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
      'connection-message': () => { return import(/* webpackChunkName: "core-data-type-connection-message" */ '#/main/core/data/types/connection-message') },
      'location'          : () => { return import(/* webpackChunkName: "core-data-type-location" */           '#/main/core/data/types/location') },
      'resource'          : () => { return import(/* webpackChunkName: "core-data-type-resource" */           '#/main/core/data/types/resource') },
      'resources'         : () => { return import(/* webpackChunkName: "core-data-type-resources" */          '#/main/core/data/types/resources') },
      'room'              : () => { return import(/* webpackChunkName: "core-data-type-room" */               '#/main/core/data/types/room') },
      'template'          : () => { return import(/* webpackChunkName: "core-data-type-template" */           '#/main/core/data/types/template') },
      'template_type'     : () => { return import(/* webpackChunkName: "core-data-type-template-type" */      '#/main/core/data/types/template-type') },
      'workspace'         : () => { return import(/* webpackChunkName: "core-data-type-workspace" */          '#/main/core/data/types/workspace') },
      'workspaces'        : () => { return import(/* webpackChunkName: "core-data-type-workspaces" */         '#/main/core/data/types/workspaces') }
    },
    sources: {
      'resources'    : () => { return import(/* webpackChunkName: "core-data-source-resources" */    '#/main/core/data/sources/resources') },
      'workspaces'   : () => { return import(/* webpackChunkName: "core-data-source-workspaces" */   '#/main/core/data/sources/workspaces') },
      'my_workspaces': () => { return import(/* webpackChunkName: "core-data-source-m-workspaces" */ '#/main/core/data/sources/workspaces') },
      'admin_tools'  : () => { return import(/* webpackChunkName: "core-data-source-admin-tools" */  '#/main/core/data/sources/admin-tools') },
      'tools'        : () => { return import(/* webpackChunkName: "core-data-source-tools" */        '#/main/core/data/sources/tools') }
    }
  }
})
