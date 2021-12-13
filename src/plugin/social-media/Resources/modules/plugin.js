/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Social plugin.
 */
registry.add('IcapSocialmediaBundle', {
  actions: {
    resource: {
      'like'    : () => { return import(/* webpackChunkName: "social-action-like" */   '#/plugin/social-media/resource/actions/like') },
      'unlike'  : () => { return import(/* webpackChunkName: "social-action-unlike" */ '#/plugin/social-media/resource/actions/unlike') }
    }
  }
})
