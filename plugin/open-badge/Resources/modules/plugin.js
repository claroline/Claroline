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
    'open-badge': () => { return import(/* webpackChunkName: "plugin-admin-open-badge" */ '#/plugin/open-badge/tools/badges') },
  }
})
