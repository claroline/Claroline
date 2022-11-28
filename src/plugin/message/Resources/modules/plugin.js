/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Message plugin.
 */
registry.add('ClarolineMessageBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  header: {
    'messages': () => { return import(/* webpackChunkName: "message-header-messages" */ '#/plugin/message/header/messages') }
  },

  tools: {
    'messaging': () => { return import(/* webpackChunkName: "message-tool-messaging" */ '#/plugin/message/tools/messaging') }
  },

  actions: {
    user: {
      'add-contact': () => { return import(/* webpackChunkName: "message-action-user-send-message" */ '#/plugin/message/actions/user/add-contact') },
      'send-message': () => { return import(/* webpackChunkName: "message-action-user-send-message" */ '#/plugin/message/actions/user/send-message') }
    },
    group: {
      'send-message': () => { return import(/* webpackChunkName: "message-action-group-send-message" */ '#/plugin/message/actions/group/send-message') }
    },
    workspace: {
      'send-message': () => { return import(/* webpackChunkName: "message-action-workspace-send-message" */ '#/plugin/message/actions/workspace/send-message') }
    }
  }
})
