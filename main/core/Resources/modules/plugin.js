/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Core plugin.
 */
registry.add('core', {
  actions: {
    // all resources
    'about'    : () => { return import(/* webpackChunkName: "core-action-about" */     '#/main/core/resource/actions/about') },
    'configure': () => { return import(/* webpackChunkName: "core-action-configure" */ '#/main/core/resource/actions/configure') },
    'copy'     : () => { return import(/* webpackChunkName: "core-action-copy" */      '#/main/core/resource/actions/copy') },
    'delete'   : () => { return import(/* webpackChunkName: "core-action-delete" */    '#/main/core/resource/actions/delete') },
    'edit'     : () => { return import(/* webpackChunkName: "core-action-edit" */      '#/main/core/resource/actions/edit') },
    // 'export'   : () => { return import(/* webpackChunkName: "core-action-export" */    '#/main/core/resource/actions/export') },
    'logs'     : () => { return import(/* webpackChunkName: "core-action-logs" */      '#/main/core/resource/actions/logs') },
    'move'     : () => { return import(/* webpackChunkName: "core-action-move" */      '#/main/core/resource/actions/move') },
    'open'     : () => { return import(/* webpackChunkName: "core-action-open" */      '#/main/core/resource/actions/open') },
    // 'notes'    : () => { return import(/* webpackChunkName: "core-action-notes" */     '#/main/core/resource/actions/notes') },
    'publish'  : () => { return import(/* webpackChunkName: "core-action-publish" */   '#/main/core/resource/actions/publish') },
    'rights'   : () => { return import(/* webpackChunkName: "core-action-rights" */    '#/main/core/resource/actions/rights') },
    'unpublish': () => { return import(/* webpackChunkName: "core-action-unpublish" */ '#/main/core/resource/actions/unpublish') },

    // directory resource
    'add'      : () => { return import(/* webpackChunkName: "core-action-add" */       '#/main/core/resources/directory/actions/add') },
    'import'   : () => { return import(/* webpackChunkName: "core-action-import" */    '#/main/core/resources/directory/actions/import') }

    // file resource
    //'download' : () => { return import(/* webpackChunkName: "resource-action-download" */       '#/main/core/resources/file/actions/download') }
  },

  resources: {
    'directory': () => { return import(/* webpackChunkName: "core-resource-directory" */ '#/main/core/resources/directory') },
    'file'     : () => { return import(/* webpackChunkName: "core-resource-file" */      '#/main/core/resources/file') },
    'text'     : () => { return import(/* webpackChunkName: "core-resource-text" */      '#/main/core/resources/text') }
  },

  tools: {},

  widgets: {
    'list'  : () => { return import(/* webpackChunkName: "core-widget-list" */   '#/main/core/widget/types/list') },
    'simple': () => { return import(/* webpackChunkName: "core-widget-simple" */ '#/main/core/widget/types/simple') },
    'resource': () => { return import(/* webpackChunkName: "core-widget-resource" */ '#/main/core/widget/types/resource') }
  },

  data: {
    types: {
      'organization': () => { return import(/* webpackChunkName: "core-data-organization" */ '#/main/core/user/data/types/organization') },
      'resource'    : () => { return import(/* webpackChunkName: "core-data-resource" */     '#/main/core/resource/data/types/resource') }
    },
    sources: {
      'resources' : () => { return import(/* webpackChunkName: "core-data-resources" */  '#/main/core/data/sources/resources') },
      'users'     : () => { return import(/* webpackChunkName: "core-data-users" */      '#/main/core/data/sources/users') },
      'workspaces': () => { return import(/* webpackChunkName: "core-data-workspaces" */ '#/main/core/data/sources/workspaces') }
    }
  },

  questions: {

  },

  tinymcePlugins: {

  }
})
