/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('forum', {
  actions: {
    'post' : () => { return import(/* webpackChunkName: "plugin-forum-action-post" */ '#/plugin/forum/resources/forum/actions/post') }
  },

  resources: {
    'claroline_forum': () => { return import(/* webpackChunkName: "plugin-forum-forum-resource" */ '#/plugin/forum/resources/forum') }
  },

  data: {
    sources: {
      'forum_messages' : () => { return import(/* webpackChunkName: "forum-data-forum-messages" */  '#/plugin/forum/data/sources/forum-messages') }
    }
  }
})
