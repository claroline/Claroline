import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Tag plugin.
 */
registry.add('tag', {
  actions: {
    workspace: {
      'tags': () => { return import(/* webpackChunkName: "tag-action-workspace-tags" */ '#/plugin/tag/workspace/actions/tags') }
    },
    resource: {
      'tags': () => { return import(/* webpackChunkName: "tag-action-resource-tags" */ '#/plugin/tag/resource/actions/tags') }
    }
  }
})
