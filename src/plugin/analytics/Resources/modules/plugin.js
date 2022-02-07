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
  },

  analytics: {
    administration: {
      // move in log plugin later
      'activity': () => { return import(/* webpackChunkName: "analytics-dashboard-administration-activity" */ '#/plugin/analytics/dashboard/administration/activity') },
      // move in community plugin later
      'community': () => { return import(/* webpackChunkName: "analytics-dashboard-administration-community" */ '#/plugin/analytics/dashboard/administration/community') },
      // move in core plugin later
      'content': () => { return import(/* webpackChunkName: "analytics-dashboard-administration-content" */ '#/plugin/analytics/dashboard/administration/content') }
    },

    workspace: {
      // move in log plugin later
      'activity': () => { return import(/* webpackChunkName: "analytics-dashboard-workspace-activity" */ '#/plugin/analytics/dashboard/workspace/activity') },
      // move in community plugin later
      'community': () => { return import(/* webpackChunkName: "analytics-dashboard-workspace-community" */ '#/plugin/analytics/dashboard/workspace/community') },
      // move in core plugin later
      'content': () => { return import(/* webpackChunkName: "analytics-dashboard-workspace-content" */ '#/plugin/analytics/dashboard/workspace/content') }
    },

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
