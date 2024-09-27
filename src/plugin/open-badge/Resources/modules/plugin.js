/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineOpenBadgeBundle', {
  data: {
    types: {
      'rule' : () => { return import(/* webpackChunkName: "plugin-open-badge-data-rule" */  '#/plugin/open-badge/data/types/rule') }
    },
    sources: {
      'badges'   : () => { return import(/* webpackChunkName: "plugin-open-badge-source-badges" */    '#/plugin/open-badge/data/sources/badges') },
      'my_badges': () => { return import(/* webpackChunkName: "plugin-open-badge-source-my-badges" */ '#/plugin/open-badge/data/sources/my-badges') }
    }
  },

  actions: {
    badge: {
      'open'       : () => { return import(/* webpackChunkName: "badge-action-badge-open" */        '#/plugin/open-badge/actions/badge/open') },
      'edit'       : () => { return import(/* webpackChunkName: "badge-action-badge-edit" */        '#/plugin/open-badge/actions/badge/edit') },
      'delete'     : () => { return import(/* webpackChunkName: "badge-action-badge-delete" */      '#/plugin/open-badge/actions/badge/delete') },
      'grant'      : () => { return import(/* webpackChunkName: "badge-action-badge-grant" */       '#/plugin/open-badge/actions/badge/grant') },
      'archive'    : () => { return import(/* webpackChunkName: "badge-action-badge-enable" */      '#/plugin/open-badge/actions/badge/archive') },
      'unarchive'  : () => { return import(/* webpackChunkName: "badge-action-badge-disable" */     '#/plugin/open-badge/actions/badge/unarchive') },
      'recalculate': () => { return import(/* webpackChunkName: "badge-action-badge-recalculate" */ '#/plugin/open-badge/actions/badge/recalculate') }
    }
  },

  /**
   * Provides Administration tools.
   */
  tools: {
    'badges': () => { return import(/* webpackChunkName: "plugin-open-badge-tool-badges" */ '#/plugin/open-badge/tools/badges') },
  }
})
