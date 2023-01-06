import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Notification plugin.
 */
registry.add('IcapNotificationBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  header: {
    'notifications': () => { return import(/* webpackChunkName: "plugin-notification-header-notifications" */ '#/plugin/notification/header/notifications') }
  },

  actions: {
    resource: {
      'follow'       : () => { return import(/* webpackChunkName: "resource-action-follow" */        '#/plugin/notification/resource/actions/follow') },
      // 'followers'    : () => { return import(/* webpackChunkName: "resource-action-followers" */     '#/plugin/notification/resource/actions/followers') },
      // 'notifications': () => { return import(/* webpackChunkName: "resource-action-notifications" */ '#/plugin/notification/resource/actions/notifications') },
      'unfollow'     : () => { return import(/* webpackChunkName: "resource-action-unfollow" */      '#/plugin/notification/resource/actions/unfollow') }
    }
  },

  /**
   * Provides current user Account sections.
   */
  account: {
    'notifications': () => { return import(/* webpackChunkName: "notification-account-notifications" */ '#/plugin/notification/account/notifications') }
  }
})
