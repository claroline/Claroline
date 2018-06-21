import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Tag plugin.
 */
registry.add('tag', {
  actions: {
    // 'tags': () => { return import(/* webpackChunkName: "tag-action-tags" */ '#/plugin/tag/resource/actions/tags') }
  }
})
