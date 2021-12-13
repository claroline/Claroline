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
    },
    user: {
      'dashboard': () => { return import(/* webpackChunkName: "analytics-action-user-dashboard" */ '#/plugin/analytics/user/actions/dashboard') }
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
  },

  analytics: {
    resource: {
      'connections' : () => { return import(/* webpackChunkName: "analytics-resource-connections" */  '#/plugin/analytics/analytics/resource/connections') },
      'requirements': () => { return import(/* webpackChunkName: "analytics-resource-requirements" */ '#/plugin/analytics/analytics/resource/requirements') }
    }
  }
})
