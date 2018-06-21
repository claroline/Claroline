import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Social plugin.
 */
registry.add('social', {
  actions: {
    // 'comments': () => { return import(/* webpackChunkName: "social-action-comments" */ '#/plugin/social-media/resource/actions/comments') },
    'like'    : () => { return import(/* webpackChunkName: "social-action-like" */     '#/plugin/social-media/resource/actions/like') },
    // 'share'   : () => { return import(/* webpackChunkName: "social-action-share" */    '#/plugin/social-media/resource/actions/share') },
    'unlike'  : () => { return import(/* webpackChunkName: "social-action-unlike" */   '#/plugin/social-media/resource/actions/unlike') }
  }
})
