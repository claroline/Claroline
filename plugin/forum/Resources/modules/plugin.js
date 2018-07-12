/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('forum', {
  resources: {
    'claroline_forum': () => { return import(/* webpackChunkName: "plugin-forum-forum-resource" */ '#/plugin/forum/resources/forum') }
  }
})
