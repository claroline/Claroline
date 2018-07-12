/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the VideoPlayer plugin.
 */
registry.add('video', {
  resources: {
    'video': () => { return import(/* webpackChunkName: "video-player-video-resource" */ '#/plugin/video-player/resources/video') }
  }
})
