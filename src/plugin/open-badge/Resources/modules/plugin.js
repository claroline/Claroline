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
})
