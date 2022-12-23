/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the PeerTube plugin.
 */
registry.add('ClarolinePeerTubeBundle', {
  /**
   * Provides new types of resources.
   */
  resources: {
    'peertube_video': () => { return import(/* webpackChunkName: "peertube-resource-video" */ '#/integration/peertube/resources/video') }
  }
})
