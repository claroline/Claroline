import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Link plugin.
 */
registry.add('link', {
  actions: {
    'shortcuts': () => { return import(/* webpackChunkName: "link-action-shortcuts" */ '#/plugin/link/resource/actions/shortcuts') }
  }
})
