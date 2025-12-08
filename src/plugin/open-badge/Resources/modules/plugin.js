/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineOpenBadgeBundle', {
  data: {
    types: {
      'badge-rules' : () => { return import(/* webpackChunkName: "badge-data-badge-rules" */  '#/plugin/open-badge/data/types/badge-rules') }
    },
    sources: {
      'badges'   : () => { return import(/* webpackChunkName: "badge-source-badges" */    '#/plugin/open-badge/data/sources/badges') },
      'my_badges': () => { return import(/* webpackChunkName: "badge-source-my-badges" */ '#/plugin/open-badge/data/sources/my-badges') }
    }
  },

  actions: {
    badges: {
      'transfer-badges': () => { return import(/* webpackChunkName: "badge-action-transfer-badges" */ '#/plugin/open-badge/tools/badges/actions/transfer-badges') },
    },
    badge: {
      'open'       : () => { return import(/* webpackChunkName: "badge-action-badge-open" */        '#/plugin/open-badge/actions/badge/open') },
      'edit'       : () => { return import(/* webpackChunkName: "badge-action-badge-edit" */        '#/plugin/open-badge/actions/badge/edit') },
      'delete'     : () => { return import(/* webpackChunkName: "badge-action-badge-delete" */      '#/plugin/open-badge/actions/badge/delete') },
      'grant'      : () => { return import(/* webpackChunkName: "badge-action-badge-grant" */       '#/plugin/open-badge/actions/badge/grant') },
      'archive'    : () => { return import(/* webpackChunkName: "badge-action-badge-archive" */      '#/plugin/open-badge/actions/badge/archive') },
      'restore'  : () => { return import(/* webpackChunkName: "badge-action-badge-restore" */     '#/plugin/open-badge/actions/badge/restore') },
      'recalculate': () => { return import(/* webpackChunkName: "badge-action-badge-recalculate" */ '#/plugin/open-badge/actions/badge/recalculate') },
      'open-workspace': () => { return import(/* webpackChunkName: "badge-action-badge-open-workspace" */ '#/plugin/open-badge/actions/badge/open-workspace') }
    },
    badge_assertion: {
      'open'          : () => { return import(/* webpackChunkName: "badge-action-badge_assertion-open" */           '#/plugin/open-badge/actions/badge_assertion/open') },
      'download'      : () => { return import(/* webpackChunkName: "badge-action-badge_assertion-download" */       '#/plugin/open-badge/actions/badge_assertion/download') },
      'delete'        : () => { return import(/* webpackChunkName: "badge-action-badge_assertion-delete" */         '#/plugin/open-badge/actions/badge_assertion/delete') },
      'show-evidences': () => { return import(/* webpackChunkName: "badge-action-badge_assertion-show-evidences" */ '#/plugin/open-badge/actions/badge_assertion/show-evidences') }
    }
  },

  /**
   * Provides Administration tools.
   */
  tools: {
    'badges': () => { return import(/* webpackChunkName: "badge-tool-badges" */ '#/plugin/open-badge/tools/badges') }
  },

  /**
   * Provides tabs for the user profile.
   */
  profile: {
    'badges': () => { return import(/* webpackChunkName: "open-badge-profile-badges" */ '#/plugin/open-badge/profile/badges') }
  }
})
