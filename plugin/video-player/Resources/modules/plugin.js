/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the VideoPlayer plugin.
 */
registry.add('video', {
  files: {
    'audio/*': () => { return import(/* webpackChunkName: "plugin-audio-file-audio" */ '#/plugin/video-player/files/video') },
    'video/*': () => { return import(/* webpackChunkName: "plugin-video-file-video" */ '#/plugin/video-player/files/video') }
  }
})
