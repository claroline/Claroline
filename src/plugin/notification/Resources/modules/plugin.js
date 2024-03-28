import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Notification plugin.
 */
registry.add('IcapNotificationBundle', {
  /**
   * Provides current user Account sections.
   */
  account: {
    'notifications': () => { return import(/* webpackChunkName: "notification-account-notifications" */ '#/plugin/notification/account/notifications') }
  }
})
