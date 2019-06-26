/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Core plugin.
 */
registry.add('ClarolineCoreBundle', {
  /**
   * Provides menu which can be used as the main header menu.
   */
  header: {
    'workspaces': () => { return import(/* webpackChunkName: "core-header-workspaces" */ '#/main/core/header/workspaces') }
  },

  /**
   * Provides actions for base Claroline objects.
   */
  actions: {
    resource: {
      // all resources
      'about'    : () => { return import(/* webpackChunkName: "core-action-resource-about" */     '#/main/core/resource/actions/about') },
      'configure': () => { return import(/* webpackChunkName: "core-action-resource-configure" */ '#/main/core/resource/actions/configure') },
      'copy'     : () => { return import(/* webpackChunkName: "core-action-resource-copy" */      '#/main/core/resource/actions/copy') },
      'delete'   : () => { return import(/* webpackChunkName: "core-action-resource-delete" */    '#/main/core/resource/actions/delete') },
      'edit'     : () => { return import(/* webpackChunkName: "core-action-resource-edit" */      '#/main/core/resource/actions/edit') },
      'export'   : () => { return import(/* webpackChunkName: "core-action-resource-export" */    '#/main/core/resource/actions/export') },
      'logs'     : () => { return import(/* webpackChunkName: "core-action-resource-logs" */      '#/main/core/resource/actions/logs') },
      'move'     : () => { return import(/* webpackChunkName: "core-action-resource-move" */      '#/main/core/resource/actions/move') },
      'open'     : () => { return import(/* webpackChunkName: "core-action-resource-open" */      '#/main/core/resource/actions/open') },
      // 'notes'    : () => { return import(/* webpackChunkName: "core-action-resource-notes" */     '#/main/core/resource/actions/notes') },
      'publish'  : () => { return import(/* webpackChunkName: "core-action-resource-publish" */   '#/main/core/resource/actions/publish') },
      'restore'  : () => { return import(/* webpackChunkName: "core-action-resource-restore" */   '#/main/core/resource/actions/restore') },
      'rights'   : () => { return import(/* webpackChunkName: "core-action-resource-rights" */    '#/main/core/resource/actions/rights') },
      'unpublish': () => { return import(/* webpackChunkName: "core-action-resource-unpublish" */ '#/main/core/resource/actions/unpublish') },

      // directory resource
      'add'       : () => { return import(/* webpackChunkName: "core-action-resource-add" */       '#/main/core/resources/directory/actions/add') },
      //'import'    : () => { return import(/* webpackChunkName: "core-action-resource-import" */    '#/main/core/resources/directory/actions/import') },
      'add_files' : () => { return import(/* webpackChunkName: "core-action-resource-add-files" */ '#/main/core/resources/directory/actions/add-files') },

      // file resource
      //'download' : () => { return import(/* webpackChunkName: "core-action-resource-download" */       '#/main/core/resources/file/actions/download') },
      'change_file' : () => { return import(/* webpackChunkName: "core-action-resource-change-file" */ '#/main/core/resources/file/actions/change-file') }
    },

    workspace: {
      'about'          : () => { return import(/* webpackChunkName: "core-action-workspace-about" */           '#/main/core/workspace/actions/about') },
      'configure'      : () => { return import(/* webpackChunkName: "core-action-workspace-configure" */       '#/main/core/workspace/actions/configure') },
      'copy'           : () => { return import(/* webpackChunkName: "core-action-workspace-copy" */            '#/main/core/workspace/actions/copy') },
      'copy-model'     : () => { return import(/* webpackChunkName: "core-action-workspace-copy-model" */      '#/main/core/workspace/actions/copy-model') },
      'delete'         : () => { return import(/* webpackChunkName: "core-action-workspace-delete" */          '#/main/core/workspace/actions/delete') },
      'export'         : () => { return import(/* webpackChunkName: "core-action-workspace-export" */          '#/main/core/workspace/actions/export') },
      'open'           : () => { return import(/* webpackChunkName: "core-action-workspace-open" */            '#/main/core/workspace/actions/open') },
      'register-users' : () => { return import(/* webpackChunkName: "core-action-workspace-register-users" */  '#/main/core/workspace/actions/register-users') },
      'register-groups': () => { return import(/* webpackChunkName: "core-action-workspace-register-groups" */ '#/main/core/workspace/actions/register-groups') },
      'register-self'  : () => { return import(/* webpackChunkName: "core-action-workspace-register-self" */   '#/main/core/workspace/actions/register-self') },
      'unregister-self': () => { return import(/* webpackChunkName: "core-action-workspace-unregister-self" */ '#/main/core/workspace/actions/unregister-self') },
      'view-as'        : () => { return import(/* webpackChunkName: "core-action-workspace-view-as" */         '#/main/core/workspace/actions/view-as') }
    },

    user: {
      'disable'        : () => { return import(/* webpackChunkName: "core-action-user-disable" */         '#/main/core/user/actions/disable') },
      'enable'         : () => { return import(/* webpackChunkName: "core-action-user-enable" */          '#/main/core/user/actions/enable') },
      'password-change': () => { return import(/* webpackChunkName: "core-action-user-password-change" */ '#/main/core/user/actions/password-change') },
      'password-reset' : () => { return import(/* webpackChunkName: "core-action-user-password-reset" */  '#/main/core/user/actions/password-reset') },
      'show-as'        : () => { return import(/* webpackChunkName: "core-action-user-show-as" */         '#/main/core/user/actions/show-as') },
      'show-profile'   : () => { return import(/* webpackChunkName: "core-action-user-show-profile" */    '#/main/core/user/actions/show-profile') },
      'show-tracking'  : () => { return import(/* webpackChunkName: "core-action-user-show-tracking" */   '#/main/core/user/actions/show-tracking') },
      'ws-disable'     : () => { return import(/* webpackChunkName: "core-action-user-ws-disable" */      '#/main/core/user/actions/ws-disable') },
      'ws-enable'      : () => { return import(/* webpackChunkName: "core-action-user-ws-enable" */       '#/main/core/user/actions/ws-enable') },
      'merge'          : () => { return import(/* webpackChunkName: "core-action-user-ws-merge" */        '#/main/core/user/actions/merge') }
    },
    group: {

    }
  },

  /**
   * Provides new types of resources
   */
  resources: {
    'directory': () => { return import(/* webpackChunkName: "core-resource-directory" */ '#/main/core/resources/directory') },
    'file'     : () => { return import(/* webpackChunkName: "core-resource-file" */      '#/main/core/resources/file') },
    'text'     : () => { return import(/* webpackChunkName: "core-resource-text" */      '#/main/core/resources/text') }
  },

  tools: {},

  widgets: {
    'list'       : () => { return import(/* webpackChunkName: "core-widget-list" */        '#/main/core/widget/types/list') },
    'simple'     : () => { return import(/* webpackChunkName: "core-widget-simple" */      '#/main/core/widget/types/simple') },
    'resource'   : () => { return import(/* webpackChunkName: "core-widget-resource" */    '#/main/core/widget/types/resource') },
    'profile'    : () => { return import(/* webpackChunkName: "core-widget-profile" */     '#/main/core/widget/types/profile') },
    'progression': () => { return import(/* webpackChunkName: "core-widget-progression" */ '#/main/core/widget/types/progression') }
  },

  data: {
    types: {
      'organization' : () => { return import(/* webpackChunkName: "core-data-organization" */  '#/main/core/data/types/organization') },
      'resource'     : () => { return import(/* webpackChunkName: "core-data-resource" */      '#/main/core/data/types/resource') },
      'resources'    : () => { return import(/* webpackChunkName: "core-data-resources" */     '#/main/core/data/types/resources') },
      'user'         : () => { return import(/* webpackChunkName: "core-data-user" */          '#/main/core/data/types/user') },
      'users'        : () => { return import(/* webpackChunkName: "core-data-users" */         '#/main/core/data/types/users') },
      'workspace'    : () => { return import(/* webpackChunkName: "core-data-workspace" */     '#/main/core/data/types/workspace') },
      'workspaces'   : () => { return import(/* webpackChunkName: "core-data-workspaces" */    '#/main/core/data/types/workspaces') },
      'groups'       : () => { return import(/* webpackChunkName: "core-data-groups" */        '#/main/core/data/types/groups') },
      'group'        : () => { return import(/* webpackChunkName: "core-data-group" */         '#/main/core/data/types/group') },
      'location'     : () => { return import(/* webpackChunkName: "core-data-location" */      '#/main/core/data/types/location') },
      'template_type': () => { return import(/* webpackChunkName: "core-data-template-type" */ '#/main/core/data/types/template-type') },
      'roles'        : () => { return import(/* webpackChunkName: "core-data-roles" */         '#/main/core/data/types/roles') }
    },
    sources: {
      'resources'    : () => { return import(/* webpackChunkName: "core-data-resources" */  '#/main/core/data/sources/resources') },
      'users'        : () => { return import(/* webpackChunkName: "core-data-users" */      '#/main/core/data/sources/users') },
      'workspaces'   : () => { return import(/* webpackChunkName: "core-data-workspaces" */ '#/main/core/data/sources/workspaces') },
      'my_workspaces': () => { return import(/* webpackChunkName: "core-data-workspaces" */ '#/main/core/data/sources/workspaces') }
    }
  },

  questions: {

  },

  tinymcePlugins: {

  }
})
