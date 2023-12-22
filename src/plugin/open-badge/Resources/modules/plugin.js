/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineOpenBadgeBundle', {
  data: {
    types: {
      'badge': () => { return import(/* webpackChunkName: "plugin-open-badge-data-badge" */ '#/plugin/open-badge/data/types/badge') },
      'rule' : () => { return import(/* webpackChunkName: "plugin-open-badge-data-rule" */  '#/plugin/open-badge/data/types/rule') }
    },
    sources: {
      'badges'   : () => { return import(/* webpackChunkName: "plugin-open-badge-source-badges" */    '#/plugin/open-badge/data/sources/badges') },
      'my_badges': () => { return import(/* webpackChunkName: "plugin-open-badge-source-my-badges" */ '#/plugin/open-badge/data/sources/my-badges') }
    }
  },

  /**
   * Provides Administration tools.
   */
  tools: {
    'badges': () => { return import(/* webpackChunkName: "plugin-open-badge-tool-badges" */ '#/plugin/open-badge/tools/badges') },
  },

  /**
   * Provides current user Account sections.
   */
  account: {
    'badges': () => { return import(/* webpackChunkName: "plugin-open-badge-account-badges" */ '#/plugin/open-badge/account/badges') },
  },

  actions: {
    badge: {
      'open'       : () => { return import(/* webpackChunkName: "badge-action-badge-open" */        '#/plugin/open-badge/actions/badge/open') },
      'edit'       : () => { return import(/* webpackChunkName: "badge-action-badge-edit" */        '#/plugin/open-badge/actions/badge/edit') },
      'delete'     : () => { return import(/* webpackChunkName: "badge-action-badge-delete" */      '#/plugin/open-badge/actions/badge/delete') },
      'grant'      : () => { return import(/* webpackChunkName: "badge-action-badge-grant" */       '#/plugin/open-badge/actions/badge/grant') },
      'enable'     : () => { return import(/* webpackChunkName: "badge-action-badge-enable" */      '#/plugin/open-badge/actions/badge/enable') },
      'disable'    : () => { return import(/* webpackChunkName: "badge-action-badge-disable" */     '#/plugin/open-badge/actions/badge/disable') },
      'recalculate': () => { return import(/* webpackChunkName: "badge-action-badge-recalculate" */ '#/plugin/open-badge/actions/badge/recalculate') }
    },
  }
})
