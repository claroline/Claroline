/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineOpenBadgeBundle', {
  data: {
    types: {
      'badge' : () => { return import(/* webpackChunkName: "plugin-open-badge-data-badge" */  '#/plugin/open-badge/tools/badges/data/types/badge') }
    }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'open-badge'      : () => { return import(/* webpackChunkName: "plugin-admin-open-badge" */          '#/plugin/open-badge/tools/badges') },
  },

  /**
   * Provides Administration tools.
   */
  tools: {
    'open-badge'      : () => { return import(/* webpackChunkName: "plugin-admin-open-badge" */          '#/plugin/open-badge/tools/badges') },
  }
})
