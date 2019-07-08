/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Message plugin.
 */
registry.add('ClarolineMessageBundle', {
  tools: {
    'messaging': () => { return import(/* webpackChunkName: "message-tool-messaging" */ '#/plugin/message/tools/messaging') }
  },

  user: {
    'add-contact' : () => { return import(/* webpackChunkName: "message-action-user-add-contact" */  '#/plugin/message/user/actions/add-contact') },
    'send-message': () => { return import(/* webpackChunkName: "message-action-user-send-message" */ '#/plugin/message/user/actions/send-message') }
  }
})
