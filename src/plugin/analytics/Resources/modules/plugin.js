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

  analytics: {
    resource: {
      // move in log plugin later
      'activity': () => { return import(/* webpackChunkName: "analytics-dashboard-resource-activity" */ '#/plugin/analytics/dashboard/resource/activity') },
      // move in community plugin later
      //'community': () => { return import(/* webpackChunkName: "analytics-dashboard-resource-community" */ '#/plugin/analytics/dashboard/resource/community') },
      // move in core plugin later
      //'content': () => { return import(/* webpackChunkName: "analytics-dashboard-resource-content" */ '#/plugin/analytics/dashboard/resource/content') }
    }
  }
})
