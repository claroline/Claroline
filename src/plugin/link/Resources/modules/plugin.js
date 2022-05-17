import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Link plugin.
 */
registry.add('ClarolineLinkBundle', {
  /**
   * Provides tab types for Home tools.
   */
  home: {
    'tool_shortcut': () => { return import(/* webpackChunkName: "link-home-tool_shortcut" */ '#/plugin/link/home/tool-shortcut') }
  },

  actions: {
    resource: {
      // 'shortcuts': () => { return import(/* webpackChunkName: "link-action-shortcuts" */ '#/plugin/link/resource/actions/shortcuts') }
    }
  },

  resources: {
    'shortcut': () => { return import(/* webpackChunkName: "plugin-link-resource" */ '#/plugin/link/resources/shortcut') }
  }
})
