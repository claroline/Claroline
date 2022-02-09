/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Analytics plugin.
 */
registry.add('ClarolineAnalyticsBundle', {
  /**
   * Provides actions for base Claroline objects.
   */
  actions: {
    resource: {
      // all resources
      'dashboard': () => { return import(/* webpackChunkName: "analytics-action-resource-dashboard" */ '#/plugin/analytics/resource/actions/dashboard') }
    }
  },

  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'dashboard': () => { return import(/* webpackChunkName: "analytics-tool-dashboard" */ '#/plugin/analytics/tools/dashboard') }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'dashboard': () => { return import(/* webpackChunkName: "analytics-admin-dashboard" */ '#/plugin/analytics/administration/dashboard') }
  }
})
