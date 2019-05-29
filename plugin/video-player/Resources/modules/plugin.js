/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the VideoPlayer plugin.
 */
registry.add('ClarolineVideoPlayerBundle', {
  files: {
    'video/*': () => { return import(/* webpackChunkName: "plugin-video-file-video" */ '#/plugin/video-player/files/video') }
  }
})
