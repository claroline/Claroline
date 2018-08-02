/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the PlannedNotification plugin.
 */
registry.add('planned-notification', {
  data: {
    types: {
      'workspace_roles': () => { return import(/* webpackChunkName: "planned-notification-data-workspace_roles" */ '#/plugin/planned-notification/data/roles') },
      'message'        : () => { return import(/* webpackChunkName: "planned-notification-data-message" */         '#/plugin/planned-notification/data/message') }
    }
  }
})
