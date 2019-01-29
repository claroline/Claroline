/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the PlannedNotification plugin.
 */
registry.add('ClarolinePlannedNotificationBundle', {
  data: {
    types: {
      'workspace_roles'       : () => { return import(/* webpackChunkName: "planned-notification-data-workspace_roles" */ '#/plugin/planned-notification/data/types/roles') },
      'message'               : () => { return import(/* webpackChunkName: "planned-notification-data-message" */         '#/plugin/planned-notification/data/types/message') },
      'planned_notifications' : () => { return import(/* webpackChunkName: "planned-notification-data-notifications" */   '#/plugin/planned-notification/data/types/notifications') }
    }
  }
})
